<?php
session_start();

$id_producto = $_GET['id'] ?? 0;

if(isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = array_filter($_SESSION['wishlist'], function($id) use ($id_producto) {
        return $id != $id_producto;
    });
    $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
}

header("Location: wishlist.php");
exit();
?>
