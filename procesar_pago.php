<?php
session_start();
include("conexion.php");

if($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit();
}

if(empty($_POST['metodo_pago'])) {
    header("Location: metodos_pago.php");
    exit();
}

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?error=Debes iniciar sesión para completar la compra");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$metodo_pago = $_POST['metodo_pago'];

// Calcular total
$total = 0;
$cantidad_total = 0;
$detalles_carrito = [];
foreach($_SESSION['carrito'] as $id => $item) {
    $subtotal_item = $item['precio'] * $item['cantidad'];
    $total += $subtotal_item;
    $cantidad_total += $item['cantidad'];
    $detalles_carrito[] = [
        'id' => $id,
        'nombre' => $item['nombre'],
        'precio' => $item['precio'],
        'cantidad' => $item['cantidad'],
        'subtotal' => $subtotal_item,
        'imagen' => $item['imagen']
    ];
}

// Aplicar descuento del juego si existe
$descuento_juego_pct = isset($_POST['descuento_aplicado']) ? intval($_POST['descuento_aplicado']) : (isset($_SESSION['juego_descuento']) ? intval($_SESSION['juego_descuento']) : 0);
if ($descuento_juego_pct > 0) {
    $descuento_total = round($total * $descuento_juego_pct / 100, 2);
    $total = $total - $descuento_total;
}

// Limpiar estado del juego después de pagar
// REGLA: Cuando paga $100+, obtiene 1 INTENTO
if ($total >= 100) {
    $_SESSION['compra_pagada'] = true;  // Marca que ya pagó $100+
    $_SESSION['intento_disponible'] = 1;  // Obtiene 1 intento
    unset($_SESSION['compra_requerida']);  // Limpia bloqueo anterior
}
unset($_SESSION['juego_descuento'], $_SESSION['juego_ganado'], $_SESSION['juego_perdido']);

$estado = 'pendiente';
$numero_tarjeta = null;
$nombre_tarjeta = null;
$fecha_vencimiento = null;
$cvv = null;
$tienda_cercana = null;
$iddireccion = null;

// Obtener dirección de entrega
$direccion_entrega = null;
$iddireccion = isset($_POST['iddireccion']) ? intval($_POST['iddireccion']) : 0;
if($iddireccion) {
    $sql_dir = "SELECT * FROM direcciones_entrega WHERE iddireccion = ? AND idusuarios = ?";
    $stmt_dir = $conexion->prepare($sql_dir);
    if($stmt_dir) {
        $stmt_dir->bind_param("ii", $iddireccion, $usuario_id);
        $stmt_dir->execute();
        $resultado_dir = $stmt_dir->get_result();
        if($resultado_dir->num_rows > 0) {
            $direccion_entrega = $resultado_dir->fetch_assoc();
        }
        $stmt_dir->close();
    }
}

// Validar método de pago
if($metodo_pago === 'tarjeta') {
    $numero_tarjeta = isset($_POST['numero_tarjeta']) ? preg_replace('/\s+/', '', $_POST['numero_tarjeta']) : '';
    $nombre_tarjeta = isset($_POST['nombre_tarjeta']) ? $_POST['nombre_tarjeta'] : '';
    $fecha_vencimiento = isset($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : '';
    $cvv = isset($_POST['cvv']) ? $_POST['cvv'] : '';
    $estado = 'pagado'; // Cambiar a pagado directamente para tarjeta
} elseif($metodo_pago === 'tienda') {
    $tienda_cercana = isset($_POST['tienda_cercana']) ? $_POST['tienda_cercana'] : '';
    $estado = 'pendiente_pago_tienda';
}

// Insertar en tabla ordenes
$sql_orden = "INSERT INTO ordenes (idusuario, total, estado, metodo_pago, numero_tarjeta, nombre_tarjeta, fecha_vencimiento, tienda_cercana, detalles_orden) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt_orden = $conexion->prepare($sql_orden);
if(!$stmt_orden) {
    die("Error en la preparación de la orden: " . $conexion->error);
}

$detalles_json = json_encode($detalles_carrito);
$stmt_orden->bind_param("idsssssss", $usuario_id, $total, $estado, $metodo_pago, $numero_tarjeta, $nombre_tarjeta, $fecha_vencimiento, $tienda_cercana, $detalles_json);

if(!$stmt_orden->execute()) {
    die("Error guardando la orden: " . $stmt_orden->error);
}

$idorden = $stmt_orden->insert_id;
$stmt_orden->close();

// Guardar ubicación GPS del almacén/origen del producto
// Coordenadas por defecto del almacén principal
$gps_almacen_calle = "Av. Principal";
$gps_almacen_numero = "1234";
$gps_almacen_ciudad = "Montevideo";
$gps_almacen_estado = "Montevideo";
$gps_almacen_pais = "Uruguay";
$gps_almacen_latitud = -33.856159;
$gps_almacen_longitud = -56.167758;

// Insertar o actualizar dirección de entrega con GPS del almacén
$sql_dir_almacen = "INSERT INTO direcciones_entrega (idusuarios, calle, numero, ciudad, estado, pais, latitud, longitud) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    calle = VALUES(calle), numero = VALUES(numero), ciudad = VALUES(ciudad), 
                    estado = VALUES(estado), pais = VALUES(pais), latitud = VALUES(latitud), longitud = VALUES(longitud)";

$stmt_dir_almacen = $conexion->prepare($sql_dir_almacen);
if($stmt_dir_almacen) {
    $stmt_dir_almacen->bind_param("isssssdd", $usuario_id, $gps_almacen_calle, $gps_almacen_numero, $gps_almacen_ciudad, $gps_almacen_estado, $gps_almacen_pais, $gps_almacen_latitud, $gps_almacen_longitud);
    $stmt_dir_almacen->execute();
    $stmt_dir_almacen->close();
}

// Insertar detalles de la orden
$sql_detalles = "INSERT INTO detalles_orden (idorden, idproducto, nombre_producto, precio, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_detalles = $conexion->prepare($sql_detalles);

foreach($detalles_carrito as $detalle) {
    $stmt_detalles->bind_param("iisidi", $idorden, $detalle['id'], $detalle['nombre'], $detalle['precio'], $detalle['cantidad'], $detalle['subtotal']);
    if(!$stmt_detalles->execute()) {
        die("Error guardando detalles: " . $stmt_detalles->error);
    }
}
$stmt_detalles->close();

// Generar código de barras si es pago en tienda
$codigo_barras = '';
if($metodo_pago === 'tienda') {
    $prefijo = "OED";
    $codigo_barras = $prefijo . $idorden . rand(1000, 9999);
    
    $sql_actualizar_codigo = "UPDATE ordenes SET codigo_barras = ? WHERE idorden = ?";
    $stmt_codigo = $conexion->prepare($sql_actualizar_codigo);
    if($stmt_codigo) {
        $stmt_codigo->bind_param("si", $codigo_barras, $idorden);
        $stmt_codigo->execute();
        $stmt_codigo->close();
    }
}

// Guardar en sesión
$_SESSION['idorden'] = $idorden;
$_SESSION['codigo_barras'] = $codigo_barras;
$_SESSION['metodo_pago'] = $metodo_pago;
$_SESSION['tienda_cercana'] = $tienda_cercana;
$_SESSION['total_compra'] = $total;
$_SESSION['detalles_compra'] = $detalles_carrito;
$_SESSION['direccion_entrega'] = $direccion_entrega;

unset($_SESSION['carrito']);
$conexion->close();

header("Location: confirmacion_pago.php");
exit();
?>
