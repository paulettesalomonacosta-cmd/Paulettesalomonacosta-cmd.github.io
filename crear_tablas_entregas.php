<?php
include("conexion.php");

$sql = "CREATE TABLE IF NOT EXISTS direcciones_entrega (
    iddireccion INT AUTO_INCREMENT PRIMARY KEY,
    idusuarios INT NOT NULL,
    calle VARCHAR(255) NOT NULL,
    numero VARCHAR(20),
    apartamento VARCHAR(20),
    ciudad VARCHAR(100) NOT NULL,
    estado VARCHAR(100),
    codigo_postal VARCHAR(20),
    pais VARCHAR(100),
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    referencia TEXT,
    es_predeterminada BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idusuarios) REFERENCES usuarios(idusuarios) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conexion->query($sql) === TRUE) {
    echo "Tabla direcciones_entrega creada o ya existe.";
} else {
    echo "Error al crear la tabla: " . $conexion->error;
}
?>
