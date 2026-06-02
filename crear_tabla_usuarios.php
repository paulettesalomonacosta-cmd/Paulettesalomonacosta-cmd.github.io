<?php
include("conexion.php");

// Crear tabla de usuarios si no existe
$sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
    idusuarios INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if($conexion->query($sql_usuarios)) {
    echo "<h2 style='color: green;'>✓ Tabla de usuarios creada correctamente</h2>";
} else {
    echo "<h2 style='color: red;'>✗ Error al crear tabla: " . $conexion->error . "</h2>";
}

$conexion->close();
?>
