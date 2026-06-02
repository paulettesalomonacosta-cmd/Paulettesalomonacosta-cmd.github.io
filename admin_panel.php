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

// Obtener estadísticas
$sql_total = "SELECT COUNT(*) as total FROM productos";
$resultado_total = $conexion->query($sql_total);
$total_productos = $resultado_total->fetch_assoc()['total'];

$sql_sin_imagen = "SELECT COUNT(*) as sin_imagen FROM productos WHERE imagen = '' OR imagen IS NULL";
$resultado_sin_imagen = $conexion->query($sql_sin_imagen);
$productos_sin_imagen = $resultado_sin_imagen->fetch_assoc()['sin_imagen'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de Administración - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Arial', sans-serif;
        padding: 40px 20px;
    }

    .admin-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .admin-header {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        margin-bottom: 30px;
        text-align: center;
    }

    .admin-header h1 {
        color: #000000;
        margin-bottom: 10px;
        font-size: 28px;
    }

    .admin-header p {
        color: #666;
        font-size: 14px;
    }

    .volver-btn {
        display: inline-block;
        margin-bottom: 20px;
        padding: 10px 20px;
        background: #000000;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s;
        margin-right: 10px;
    }

    .volver-btn:hover {
        background: #004080;
        transform: translateY(-2px);
    }

    .btn-logout {
        display: inline-block;
        padding: 8px 16px;
        background: #ff5500;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s;
        font-weight: bold;
        font-size: 14px;
    }

    .btn-logout:hover {
        background: #ff3d00;
        transform: translateY(-2px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        text-align: center;
    }

    .stat-card i {
        font-size: 30px;
        color: #ff5500;
        margin-bottom: 10px;
    }

    .stat-card h3 {
        font-size: 24px;
        color: #000000;
        margin-bottom: 5px;
    }

    .stat-card p {
        color: #666;
        font-size: 12px;
    }

    .admin-menu {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .admin-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        transition: all 0.3s;
        cursor: pointer;
    }

    .admin-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.25);
    }

    .admin-card-header {
        padding: 30px 20px;
        text-align: center;
        font-size: 40px;
    }

    .admin-card-1 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .admin-card-2 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .admin-card-3 {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .admin-card-4 {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .admin-card-content {
        padding: 0 20px 20px 20px;
        text-align: center;
    }

    .admin-card h3 {
        color: white;
        margin-bottom: 10px;
        font-size: 20px;
    }

    .admin-card p {
        color: rgba(255,255,255,0.9);
        font-size: 13px;
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .admin-card-btn {
        display: inline-block;
        padding: 12px 25px;
        background: white;
        color: #000000;
        text-decoration: none;
        border-radius: 25px;
        font-weight: bold;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .admin-card-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .admin-card-2 .admin-card-btn {
        color: #f5576c;
    }

    .admin-card-3 .admin-card-btn {
        color: #4facfe;
    }

    .admin-card-4 .admin-card-btn {
        color: #43e97b;
    }

    .info-box {
        background: white;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #ff5500;
        margin-bottom: 20px;
    }

    .info-box h4 {
        color: #000000;
        margin-bottom: 10px;
    }

    .info-box p {
        color: #666;
        font-size: 13px;
        line-height: 1.6;
    }

    .footer-admin {
        text-align: center;
        color: white;
        margin-top: 30px;
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .admin-menu {
            grid-template-columns: 1fr;
        }

        .admin-header h1 {
            font-size: 20px;
        }
    }
</style>
</head>

<body>

<div class="admin-container">
    <a href="index.php" class="volver-btn">
        <i class="fas fa-arrow-left"></i> Volver a la Tienda
    </a>

    <div class="admin-header">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <div>
                <h1><i class="fas fa-cogs"></i> Panel de Administración</h1>
                <p>Gestiona tus productos y actualiza imágenes fácilmente</p>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0 0 10px 0; color: #666;">Bienvenido, <strong><?php echo $_SESSION['usuario_nombre']; ?></strong></p>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>

    <!-- ESTADÍSTICAS -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-box"></i>
            <h3><?php echo $total_productos; ?></h3>
            <p>Productos Totales</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-image"></i>
            <h3><?php echo $productos_sin_imagen; ?></h3>
            <p>Sin Imagen</p>
        </div>
    </div>

    <!-- INFORMACIÓN -->
    <div class="info-box">
        <h4><i class="fas fa-lightbulb"></i> ¿Cómo usar?</h4>
        <p>
            Usa <strong>"Actualizar Imágenes"</strong> para agregar o cambiar fotos de productos existentes.
            Usa <strong>"Agregar Producto"</strong> para crear nuevos productos. 
            Cada producto debe tener un link de imagen (URL).
        </p>
    </div>

    <!-- MENÚ PRINCIPAL -->
    <div class="admin-menu">
        <!-- Agregar Producto -->
        <div class="admin-card">
            <div class="admin-card-header admin-card-1">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div class="admin-card-content">
                <h3>Agregar Producto</h3>
                <p>Crea un nuevo producto con todos sus detalles, precio, stock e imagen.</p>
                <a href="agregar.php" class="admin-card-btn">
                    <i class="fas fa-plus"></i> Agregar Nuevo
                </a>
            </div>
        </div>

        <!-- Actualizar Imágenes -->
        <div class="admin-card">
            <div class="admin-card-header admin-card-2">
                <i class="fas fa-images"></i>
            </div>
            <div class="admin-card-content">
                <h3>Actualizar Imágenes</h3>
                <p>Agrega o cambia imágenes de todos tus productos existentes. ⭐ VAS AQUÍ</p>
                <a href="actualizar_imagenes.php" class="admin-card-btn">
                    <i class="fas fa-photo-video"></i> Actualizar
                </a>
            </div>
        </div>

        <!-- Editar Producto -->
        <div class="admin-card">
            <div class="admin-card-header admin-card-3">
                <i class="fas fa-edit"></i>
            </div>
            <div class="admin-card-content">
                <h3>Editar Producto</h3>
                <p>Modifica nombre, descripción, precio, stock y otros detalles de productos.</p>
                <a href="index.php?editar=1" class="admin-card-btn">
                    <i class="fas fa-pencil-alt"></i> Editar
                </a>
            </div>
        </div>

        <!-- Ver Tienda -->
        <div class="admin-card">
            <div class="admin-card-header admin-card-4">
                <i class="fas fa-store"></i>
            </div>
            <div class="admin-card-content">
                <h3>Ver Tienda</h3>
                <p>Regresa a la tienda para ver cómo se ven tus productos con las imágenes.</p>
                <a href="index.php" class="admin-card-btn">
                    <i class="fas fa-eye"></i> Ver Tienda
                </a>
            </div>
        </div>

        <!-- Limpiar Duplicados -->
        <div class="admin-card">
            <div class="admin-card-header" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-broom"></i>
            </div>
            <div class="admin-card-content">
                <h3>Limpiar Duplicados</h3>
                <p>Elimina productos duplicados y mantén solo un ejemplar de cada uno.</p>
                <a href="limpiar_duplicados_simple.php" class="admin-card-btn" style="color: #fa709a;">
                    <i class="fas fa-check"></i> Limpiar Ahora
                </a>
            </div>
        </div>

        <!-- Configurar Carrusel -->
        <div class="admin-card">
            <div class="admin-card-header" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
                <i class="fas fa-images"></i>
            </div>
            <div class="admin-card-content">
                <h3>Configurar Carrusel</h3>
                <p>Edita fácilmente los 5 links de imágenes que aparecen en el carrusel de inicio.</p>
                <a href="configurar_carrusel.php" class="admin-card-btn" style="color: #ff6b6b;">
                    <i class="fas fa-sliders-h"></i> Configurar
                </a>
            </div>
        </div>
    </div>

    <div class="footer-admin">
        <p>MAXIMA ONLINE STORE © 2024 - Panel de Administración</p>
    </div>
</div>

</body>
</html>
