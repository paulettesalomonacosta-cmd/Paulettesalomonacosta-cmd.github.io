<?php
include("conexion.php");

echo "<h1>Verificación de Base de Datos</h1>";
echo "<hr>";

// 1. Verificar tabla ordenes
echo "<h2>1. Verificando tabla 'ordenes':</h2>";
$result = $conexion->query("DESCRIBE ordenes");
if($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Tabla 'ordenes' existe</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Tabla 'ordenes' no existe</p>";
}

echo "<hr>";

// 2. Verificar tabla direcciones_entrega
echo "<h2>2. Verificando tabla 'direcciones_entrega':</h2>";
$result = $conexion->query("DESCRIBE direcciones_entrega");
if($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Tabla 'direcciones_entrega' existe</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Tabla 'direcciones_entrega' no existe</p>";
}

echo "<hr>";

// 3. Verificar tabla detalles_orden
echo "<h2>3. Verificando tabla 'detalles_orden':</h2>";
$result = $conexion->query("DESCRIBE detalles_orden");
if($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Tabla 'detalles_orden' existe</p>";
} else {
    echo "<p style='color: red;'>✗ Tabla 'detalles_orden' no existe</p>";
}

echo "<hr>";

// 4. Contar órdenes
echo "<h2>4. Estadísticas:</h2>";
$result = $conexion->query("SELECT COUNT(*) as count FROM ordenes");
if($result) {
    $row = $result->fetch_assoc();
    echo "<p>Total de órdenes: " . $row['count'] . "</p>";
}

echo "<hr>";

// 5. Agregar columna iddireccion si no existe
echo "<h2>5. Intentando agregar columna 'iddireccion' si no existe:</h2>";
$sql_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'ordenes' AND COLUMN_NAME = 'iddireccion'";
$resultado = $conexion->query($sql_check);

if($resultado && $resultado->num_rows == 0) {
    echo "<p>Columna 'iddireccion' no existe, agregando...</p>";
    if($conexion->query("ALTER TABLE ordenes ADD COLUMN iddireccion INT AFTER idusuario")) {
        echo "<p style='color: green;'>✓ Columna 'iddireccion' agregada exitosamente</p>";
        
        // Agregar Foreign Key
        if($conexion->query("ALTER TABLE ordenes ADD CONSTRAINT fk_ordenes_direccion FOREIGN KEY (iddireccion) REFERENCES direcciones_entrega(iddireccion)")) {
            echo "<p style='color: green;'>✓ Relación creada exitosamente</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Error al agregar columna: " . $conexion->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Columna 'iddireccion' ya existe</p>";
}

$conexion->close();
?>
