<?php
session_start();
include("conexion.php");

echo "<h1>Sistema de Pago - Diagnóstico y Reparación</h1>";

// 1. Verificar conexión a BD
echo "<h2>1. Verificando conexión a BD...</h2>";
if($conexion->connect_error) {
    echo "<p style='color: red;'><strong>ERROR:</strong> No se puede conectar a la BD</p>";
    die();
} else {
    echo "<p style='color: green;'><strong>✓ OK:</strong> Conectado a base de datos</p>";
}

// 2. Verificar si las tablas existen
echo "<h2>2. Verificando tablas...</h2>";

$tabla_ordenes = $conexion->query("SHOW TABLES LIKE 'ordenes'");
$tabla_detalles = $conexion->query("SHOW TABLES LIKE 'detalles_orden'");

if($tabla_ordenes->num_rows > 0) {
    echo "<p style='color: green;'><strong>✓ OK:</strong> Tabla 'ordenes' existe</p>";
} else {
    echo "<p style='color: red;'><strong>✗ ERROR:</strong> Tabla 'ordenes' NO existe - Creando...</p>";
    
    $sql_ordenes = "CREATE TABLE IF NOT EXISTS ordenes (
        idorden INT PRIMARY KEY AUTO_INCREMENT,
        idusuario INT,
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
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if($conexion->query($sql_ordenes)) {
        echo "<p style='color: green;'><strong>✓ CREADA:</strong> Tabla 'ordenes' creada correctamente</p>";
    } else {
        echo "<p style='color: red;'><strong>✗ ERROR:</strong> No se pudo crear tabla 'ordenes': " . $conexion->error . "</p>";
    }
}

if($tabla_detalles->num_rows > 0) {
    echo "<p style='color: green;'><strong>✓ OK:</strong> Tabla 'detalles_orden' existe</p>";
} else {
    echo "<p style='color: red;'><strong>✗ ERROR:</strong> Tabla 'detalles_orden' NO existe - Creando...</p>";
    
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
        echo "<p style='color: green;'><strong>✓ CREADA:</strong> Tabla 'detalles_orden' creada correctamente</p>";
    } else {
        echo "<p style='color: red;'><strong>✗ ERROR:</strong> No se pudo crear tabla 'detalles_orden': " . $conexion->error . "</p>";
    }
}

// 3. Crear índices
echo "<h2>3. Creando índices para mejor rendimiento...</h2>";

$indices = [
    "CREATE INDEX IF NOT EXISTS idx_usuario ON ordenes(idusuario)",
    "CREATE INDEX IF NOT EXISTS idx_estado ON ordenes(estado)",
    "CREATE INDEX IF NOT EXISTS idx_codigo_barras ON ordenes(codigo_barras)",
    "CREATE INDEX IF NOT EXISTS idx_fecha ON ordenes(fecha_orden)",
    "CREATE INDEX IF NOT EXISTS idx_metodo_pago ON ordenes(metodo_pago)"
];

foreach($indices as $idx) {
    if($conexion->query($idx)) {
        echo "<p style='color: green;'><strong>✓</strong> Índice creado</p>";
    }
}

// 4. Verificar si las tablas tienen estructura correcta
echo "<h2>4. Verificando estructura de tablas...</h2>";

$desc_ordenes = $conexion->query("DESCRIBE ordenes");
echo "<p style='color: green;'><strong>✓ Tabla 'ordenes':</strong> " . $desc_ordenes->num_rows . " campos</p>";

$desc_detalles = $conexion->query("DESCRIBE detalles_orden");
echo "<p style='color: green;'><strong>✓ Tabla 'detalles_orden':</strong> " . $desc_detalles->num_rows . " campos</p>";

echo "<h2 style='color: green;'>✅ SISTEMA REPARADO Y LISTO</h2>";
echo "<p>
    <a href='index.php' style='display: inline-block; margin: 10px; padding: 12px 25px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>
        ← Volver a la Tienda
    </a>
    <a href='metodos_pago.php' style='display: inline-block; margin: 10px; padding: 12px 25px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>
        Probar Sistema de Pago →
    </a>
</p>";

$conexion->close();
?>
