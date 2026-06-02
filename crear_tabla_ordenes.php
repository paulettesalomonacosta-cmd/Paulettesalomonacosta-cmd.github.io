<?php
include("conexion.php");

// Crear tabla de órdenes/pedidos si no existe
$sql_ordenes = "CREATE TABLE IF NOT EXISTS ordenes (
    idorden INT PRIMARY KEY AUTO_INCREMENT,
    idusuario INT,
    iddireccion INT,
    fecha_orden TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL,
    estado VARCHAR(50) DEFAULT 'pendiente',
    metodo_pago VARCHAR(50) NOT NULL,
    numero_tarjeta VARCHAR(20),
    nombre_tarjeta VARCHAR(100),
    fecha_vencimiento VARCHAR(10),
    codigo_barras VARCHAR(100) UNIQUE,
    tienda_cercana VARCHAR(100),
    detalles_orden JSON,
    estado_entrega VARCHAR(50) DEFAULT 'pendiente',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idusuario) REFERENCES usuarios(idusuarios),
    FOREIGN KEY (iddireccion) REFERENCES direcciones_entrega(iddireccion)
)"

if($conexion->query($sql_ordenes)) {
    echo "<h2 style='color: green;'>✓ Tabla de órdenes creada correctamente</h2>";
} else {
    echo "<h2 style='color: red;'>✗ Error al crear tabla: " . $conexion->error . "</h2>";
}

// Crear tabla de detalles de órdenes
$sql_detalles = "CREATE TABLE IF NOT EXISTS detalles_orden (
    iddetalle INT PRIMARY KEY AUTO_INCREMENT,
    idorden INT NOT NULL,
    idproducto INT NOT NULL,
    nombre_producto VARCHAR(255) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (idorden) REFERENCES ordenes(idorden)
)";

if($conexion->query($sql_detalles)) {
    echo "<h2 style='color: green;'>✓ Tabla de detalles de órdenes creada correctamente</h2>";
} else {
    echo "<h2 style='color: red;'>✗ Error al crear tabla: " . $conexion->error . "</h2>";
}

$conexion->close();
?>
