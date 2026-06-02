<?php
require 'conexion.php';

// Crear tabla de reseñas
$sql_resenas = "CREATE TABLE IF NOT EXISTS resenas (
    idresena INT AUTO_INCREMENT PRIMARY KEY,
    idproductos INT NOT NULL,
    idusuarios INT,
    calificacion INT DEFAULT 5 CHECK(calificacion >= 1 AND calificacion <= 5),
    comentario TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'aprobada',
    FOREIGN KEY (idproductos) REFERENCES productos(idproductos) ON DELETE CASCADE,
    FOREIGN KEY (idusuarios) REFERENCES usuarios(idusuarios) ON DELETE SET NULL,
    INDEX (idproductos),
    INDEX (idusuarios),
    INDEX (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conexion->query($sql_resenas) === TRUE) {
    echo "✓ Tabla 'resenas' creada/verificada correctamente.<br>";
} else {
    echo "✗ Error al crear tabla 'resenas': " . $conexion->error . "<br>";
}

$conexion->close();
?>
