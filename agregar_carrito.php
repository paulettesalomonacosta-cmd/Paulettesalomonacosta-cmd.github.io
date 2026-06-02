<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

$id_producto = $_GET['id'] ?? 0;
$cantidad = $_GET['cantidad'] ?? 1;

$sql = "SELECT * FROM productos WHERE idproductos = $id_producto";
$resultado = $conexion->query($sql);

if($resultado->num_rows > 0) {
    $producto = $resultado->fetch_assoc();
    
    if(isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
    } else {
        $_SESSION['carrito'][$id_producto] = array(
            'nombre' => $producto['nombre_producto'],
            'precio' => $producto['precio'],
            'cantidad' => $cantidad,
            'imagen' => $producto['imagen']
        );
    }
}

header("Location: carrito.php");
exit();
?>
