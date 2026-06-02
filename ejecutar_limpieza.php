<?php
include("conexion.php");

// Desactivar restricciones de clave foránea
$conexion->query("SET FOREIGN_KEY_CHECKS=0");

echo "🔍 Buscando productos duplicados...\n";

// Obtener productos duplicados
$sql = "SELECT nombre_producto, COUNT(*) as cantidad FROM productos GROUP BY nombre_producto HAVING cantidad > 1";
$resultado = $conexion->query($sql);

$productos_duplicados = [];
while($fila = $resultado->fetch_assoc()) {
    $productos_duplicados[] = $fila;
}

echo "📊 Productos con duplicados encontrados: " . count($productos_duplicados) . "\n\n";

// Eliminar duplicados: mantener el primero, eliminar el resto
$eliminados = 0;
foreach($productos_duplicados as $producto) {
    $nombre = $producto['nombre_producto'];
    $cantidad = $producto['cantidad'];
    
    // Obtener todos los IDs de este producto
    $sql_ids = "SELECT idproductos FROM productos WHERE nombre_producto = '" . $conexion->real_escape_string($nombre) . "' ORDER BY idproductos ASC";
    $resultado_ids = $conexion->query($sql_ids);
    
    $ids = [];
    while($fila_id = $resultado_ids->fetch_assoc()) {
        $ids[] = $fila_id['idproductos'];
    }
    
    // Eliminar todos EXCEPTO el primero
    if(count($ids) > 1) {
        $ids_a_eliminar = array_slice($ids, 1);
        $ids_string = implode(',', $ids_a_eliminar);
        
        $sql_delete = "DELETE FROM productos WHERE idproductos IN ($ids_string)";
        if($conexion->query($sql_delete)) {
            $cantidad_eliminada = count($ids_a_eliminar);
            $eliminados += $cantidad_eliminada;
            echo "✓ '$nombre' - Eliminados $cantidad_eliminada duplicados (Mantenido ID: " . $ids[0] . ")\n";
        }
    }
}

// Reactivar restricciones de clave foránea
$conexion->query("SET FOREIGN_KEY_CHECKS=1");

// Contar total de productos
$sql_total = "SELECT COUNT(*) as total FROM productos";
$res_total = $conexion->query($sql_total);
$total = $res_total->fetch_assoc()['total'];

echo "\n✅ LIMPIEZA COMPLETADA\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🗑️  Duplicados eliminados: $eliminados\n";
echo "📦 Productos totales ahora: $total\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$conexion->close();
?>
