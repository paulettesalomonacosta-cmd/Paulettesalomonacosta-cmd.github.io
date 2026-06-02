<?php
session_start();

$id_producto = $_GET['id'] ?? 0;

if(isset($_SESSION['carrito'][$id_producto])) {
    unset($_SESSION['carrito'][$id_producto]);
}

header("Location: carrito.php");
exit();
?>
