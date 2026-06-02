<?php
include("conexion.php");

echo "<h1 style='text-align: center; color: #000000; margin: 30px 0;'>Actualizar Base de Datos - Entregas</h1>";

// Verificar si existe el campo estado_entrega
$sql_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'ordenes' AND COLUMN_NAME = 'estado_entrega'";
$resultado = $conexion->query($sql_check);

if($resultado && $resultado->num_rows == 0) {
    echo "<p style='color: orange; padding: 10px; background: #fff3cd; border-radius: 5px;'>⚠️ Agregando campo estado_entrega...</p>";
    
    if($conexion->query("ALTER TABLE ordenes ADD COLUMN estado_entrega VARCHAR(50) DEFAULT 'pendiente'")) {
        echo "<p style='color: green; padding: 10px; background: #d4edda; border-radius: 5px;'>✓ Campo estado_entrega agregado exitosamente</p>";
    } else {
        echo "<p style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px;'>✗ Error: " . $conexion->error . "</p>";
    }
} else {
    echo "<p style='color: green; padding: 10px; background: #d4edda; border-radius: 5px;'>✓ Campo estado_entrega ya existe</p>";
}

// Actualizar ordenes antiguas para que tengan estado_entrega
$sql_actualizar = "UPDATE ordenes SET estado_entrega = CASE 
                    WHEN estado = 'entregado' THEN 'entregado'
                    WHEN estado IN ('pendiente_pago_tarjeta', 'pendiente_pago_tienda') THEN 'pendiente'
                    ELSE 'pendiente'
                   END
                   WHERE estado_entrega = 'pendiente'";

if($conexion->query($sql_actualizar)) {
    $filas_actualizadas = $conexion->affected_rows;
    echo "<p style='color: blue; padding: 10px; background: #d1ecf1; border-radius: 5px;'>ℹ️ $filas_actualizadas órdenes sincronizadas</p>";
}

echo "<hr>";

echo "<div style='text-align: center; padding: 20px;'>";
echo "<a href='admin_panel.php' style='background: #000000; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; display: inline-block;'>Volver al Panel</a>";
echo "</div>";

$conexion->close();
?>
