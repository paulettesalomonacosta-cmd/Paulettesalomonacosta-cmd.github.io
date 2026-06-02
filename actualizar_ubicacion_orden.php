<?php
session_start();
include 'conexion.php';

// Verificar que sea el administrador autorizado
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_email'] !== 'admin@maximaonline.store') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idorden = intval($_POST['idorden'] ?? 0);
    $calle = trim($_POST['calle'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $pais = trim($_POST['pais'] ?? '');
    $latitud = floatval($_POST['latitud'] ?? 0);
    $longitud = floatval($_POST['longitud'] ?? 0);
    
    if(!$idorden) {
        echo json_encode(['success' => false, 'message' => 'Orden inválida']);
        exit();
    }
    
    // Verificar que la orden existe y pertenece a un usuario
    $query = "SELECT idusuario FROM ordenes WHERE idorden = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $idorden);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
        exit();
    }
    
    $orden = $resultado->fetch_assoc();
    $idusuario = $orden['idusuario'];
    
    // Buscar o crear dirección de entrega
    $query = "SELECT iddireccion FROM direcciones_entrega WHERE idusuarios = ? LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    $resultado_dir = $stmt->get_result();
    
    if($resultado_dir->num_rows > 0) {
        // Actualizar dirección existente
        $dir = $resultado_dir->fetch_assoc();
        $iddireccion = $dir['iddireccion'];
        
        $query = "UPDATE direcciones_entrega 
                  SET calle = ?, numero = ?, ciudad = ?, estado = ?, pais = ?, latitud = ?, longitud = ? 
                  WHERE iddireccion = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ssssssi", $calle, $numero, $ciudad, $estado, $pais, $latitud, $longitud, $iddireccion);
    } else {
        // Crear nueva dirección
        $query = "INSERT INTO direcciones_entrega (idusuarios, calle, numero, ciudad, estado, pais, latitud, longitud) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("isssssdd", $idusuario, $calle, $numero, $ciudad, $estado, $pais, $latitud, $longitud);
    }
    
    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '✓ Ubicación actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    
    $stmt->close();
    exit();
}

// GET - Obtener información de dirección actual
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $idorden = intval($_GET['idorden'] ?? 0);
    
    if(!$idorden) {
        echo json_encode(['success' => false, 'message' => 'Orden inválida']);
        exit();
    }
    
    $query = "SELECT o.idorden, o.idusuario, d.calle, d.numero, d.ciudad, d.estado, d.pais, d.latitud, d.longitud
              FROM ordenes o
              LEFT JOIN direcciones_entrega d ON o.idusuario = d.idusuarios
              WHERE o.idorden = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $idorden);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
        exit();
    }
    
    $orden = $resultado->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $orden]);
    exit();
}
?>
