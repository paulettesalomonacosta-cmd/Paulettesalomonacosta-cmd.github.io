<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM usuarios WHERE idusuarios = $usuario_id";
$resultado = $conexion->query($sql);
$usuario = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Perfil - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .perfil-container {
        max-width: 600px;
        margin: 50px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .perfil-container h2 {
        color: #000000;
        margin-bottom: 20px;
        text-align: center;
    }
    .perfil-info {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .perfil-info p {
        margin: 10px 0;
        font-size: 15px;
    }
    .perfil-info strong {
        color: #000000;
    }
    .btn-logout {
        width: 100%;
        padding: 12px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
    }
    .btn-logout:hover {
        background: #c82333;
    }
    .btn-back {
        display: inline-block;
        margin-bottom: 20px;
        padding: 10px 20px;
        background: #000000;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }
    .btn-back:hover {
        background: #004080;
    }
</style>
</head>
<body>

<div class="perfil-container">
    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
    
    <h2><i class="fas fa-user"></i> Mi Perfil</h2>
    
    <div class="perfil-info">
        <p><strong>Nombre:</strong> <?php echo $usuario['nombre']; ?></p>
        <p><strong>Email:</strong> <?php echo $usuario['email']; ?></p>
        <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></p>
    </div>
    
    <a href="logout.php" style="display: block;">
        <button class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</button>
    </a>
</div>

</body>
</html>
