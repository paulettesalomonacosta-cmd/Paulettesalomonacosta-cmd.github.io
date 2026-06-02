<?php
session_start();
include("conexion.php");

echo "<h1 style='text-align: center; color: #000000; margin: 30px 0;'>Actualizar Estado de Pagos</h1>";

// Actualizar órdenes con pago por tarjeta a estado "pagado"
$sql = "UPDATE ordenes SET estado = 'pagado' WHERE estado = 'pendiente_pago_tarjeta' AND metodo_pago = 'tarjeta'";

if($conexion->query($sql)) {
    $filas = $conexion->affected_rows;
    if($filas > 0) {
        echo "<p style='color: green; padding: 15px; background: #d4edda; border-radius: 5px; text-align: center; max-width: 600px; margin: 20px auto;'>✓ $filas órdenes actualizadas a estado 'Pagado'</p>";
    } else {
        echo "<p style='color: blue; padding: 15px; background: #d1ecf1; border-radius: 5px; text-align: center; max-width: 600px; margin: 20px auto;'>ℹ️ No hay órdenes con pago pendiente de tarjeta para actualizar</p>";
    }
} else {
    echo "<p style='color: red; padding: 15px; background: #f8d7da; border-radius: 5px; text-align: center; max-width: 600px; margin: 20px auto;'>✗ Error: " . $conexion->error . "</p>";
}

echo "<hr>";

// Mostrar resumen de órdenes
$sql_resumen = "SELECT 
                SUM(CASE WHEN estado = 'pagado' THEN 1 ELSE 0 END) as pagadas,
                SUM(CASE WHEN estado = 'pendiente_pago_tarjeta' THEN 1 ELSE 0 END) as pendientes_tarjeta,
                SUM(CASE WHEN estado = 'pendiente_pago_tienda' THEN 1 ELSE 0 END) as pendientes_tienda
                FROM ordenes";

$resultado = $conexion->query($sql_resumen);
if($resultado) {
    $stats = $resultado->fetch_assoc();
    echo "<div style='max-width: 600px; margin: 20px auto;'>";
    echo "<p><strong>Resumen de Órdenes:</strong></p>";
    echo "<p>✓ Pagadas: " . ($stats['pagadas'] ?? 0) . "</p>";
    echo "<p>⏳ Pendientes pago tarjeta: " . ($stats['pendientes_tarjeta'] ?? 0) . "</p>";
    echo "<p>⏳ Pendientes pago en tienda: " . ($stats['pendientes_tienda'] ?? 0) . "</p>";
    echo "</div>";
}

echo "<div style='text-align: center; padding: 30px;'>";
echo "<a href='seguimiento_pedidos.php' style='background: #000000; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; display: inline-block; margin-right: 10px;'>Ver Seguimiento</a>";
echo "<a href='admin_panel.php' style='background: #666; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; display: inline-block;'>Panel Admin</a>";
echo "</div>";

$conexion->close();
?>
