<?php
session_start();

if(!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = array();
}

$id_producto = $_GET['id'] ?? 0;

if(!in_array($id_producto, $_SESSION['wishlist'])) {
    $_SESSION['wishlist'][] = $id_producto;
}

header("Location: " . ($_GET['return'] ?? 'index.php'));
exit();
?>
