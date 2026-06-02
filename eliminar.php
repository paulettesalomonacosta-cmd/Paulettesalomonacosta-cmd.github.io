<?php
session_start();
include("conexion.php");

// Verificar si el usuario está autenticado
if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar si es el administrador autorizado
if($_SESSION['usuario_email'] !== 'admin@maximaonline.store') {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

$sql = "DELETE FROM productos WHERE idproductos='$id'";

$conexion->query($sql);

header("Location: index.php");
exit();
?>

header("Location:index.php");

?>
