<?php
session_start();
include("conexion.php");

$mensaje = "";
$tipo_mensaje = "";

// Verificar que sea admin
if(!isset($_SESSION['admin_id'])) {
    die("Acceso denegado. Solo administradores.");
}

// Agregar columna iddireccion si no existe
$sql_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'ordenes' AND COLUMN_NAME = 'iddireccion'";
$resultado = $conexion->query($sql_check);

if($resultado && $resultado->num_rows == 0) {
    // La columna no existe, agregarla
    $sql_add = "ALTER TABLE ordenes ADD COLUMN iddireccion INT AFTER idusuario";
    if($conexion->query($sql_add)) {
        $mensaje = "✓ Columna iddireccion agregada exitosamente a la tabla ordenes";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "✗ Error al agregar columna: " . $conexion->error;
        $tipo_mensaje = "error";
    }
    
    // Agregar Foreign Key
    $sql_fk = "ALTER TABLE ordenes ADD CONSTRAINT fk_ordenes_direccion FOREIGN KEY (iddireccion) REFERENCES direcciones_entrega(iddireccion)";
    if($conexion->query($sql_fk)) {
        $mensaje .= "<br>✓ Relación con direcciones_entrega creada";
    } else {
        // Silent fail si ya existe
    }
} else {
    $mensaje = "✓ La tabla ordenes ya tiene el campo iddireccion";
    $tipo_mensaje = "info";
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Actualizar Base de Datos</title>
<link rel="stylesheet" href="estilos.css">
<style>
    .mensaje {
        padding: 20px;
        border-radius: 8px;
        margin: 20px auto;
        max-width: 500px;
        text-align: center;
    }
    .mensaje.success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    .mensaje.error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    .mensaje.info {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
    }
</style>
</head>
<body>
    <div style="margin: 50px auto; max-width: 600px;">
        <h1 style="color: #000000; text-align: center;">Actualizar Base de Datos</h1>
        
        <div class="mensaje <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="admin_panel.php" style="background: #000000; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">
                Volver al Panel
            </a>
        </div>
    </div>
</body>
</html>
