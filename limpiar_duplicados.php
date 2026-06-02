<?php
include("conexion.php");

// Desactivar restricciones de clave foránea
$conexion->query("SET FOREIGN_KEY_CHECKS=0");

// Obtener productos duplicados
$sql = "SELECT nombre_producto, COUNT(*) as cantidad FROM productos GROUP BY nombre_producto HAVING cantidad > 1";
$resultado = $conexion->query($sql);

$productos_duplicados = [];
while($fila = $resultado->fetch_assoc()) {
    $productos_duplicados[] = $fila;
}

// Eliminar duplicados: mantener el primer producto, eliminar el resto
$eliminados = 0;
foreach($productos_duplicados as $producto) {
    $nombre = $conexion->real_escape_string($producto['nombre_producto']);
    
    // Obtener todos los IDs de este producto
    $sql_ids = "SELECT idproductos FROM productos WHERE nombre_producto = '$nombre' ORDER BY idproductos ASC";
    $resultado_ids = $conexion->query($sql_ids);
    
    $ids = [];
    while($fila_id = $resultado_ids->fetch_assoc()) {
        $ids[] = $fila_id['idproductos'];
    }
    
    // Eliminar todos EXCEPTO el primero
    if(count($ids) > 1) {
        $ids_a_eliminar = array_slice($ids, 1); // Todos excepto el primero
        $ids_string = implode(',', $ids_a_eliminar);
        
        $sql_delete = "DELETE FROM productos WHERE idproductos IN ($ids_string)";
        if($conexion->query($sql_delete)) {
            $eliminados += count($ids_a_eliminar);
        }
    }
}

// Reactivar restricciones de clave foránea
$conexion->query("SET FOREIGN_KEY_CHECKS=1");

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Limpiar Duplicados - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: Arial, sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        padding: 40px;
        max-width: 500px;
        text-align: center;
    }
    
    h1 {
        color: #000000;
        margin-bottom: 20px;
        font-size: 24px;
    }
    
    .success-icon {
        font-size: 60px;
        color: #28a745;
        margin-bottom: 20px;
    }
    
    .message {
        font-size: 16px;
        color: #666;
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .stats {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    
    .stat-item {
        font-size: 18px;
        color: #000000;
        margin: 10px 0;
        font-weight: bold;
    }
    
    .stat-label {
        color: #666;
        font-size: 14px;
    }
    
    .btn {
        display: inline-block;
        padding: 12px 30px;
        background: #000000;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
    }
    
    .btn:hover {
        background: #004080;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .btn-group {
        display: flex;
        gap: 10px;
        justify-content: center;
    }
    
    .btn-secondary {
        background: #6c757d;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
</style>
</head>

<body>

<div class="container">
    <div class="success-icon">
        <i class="fas fa-check-circle"></i>
    </div>
    
    <h1>✓ Limpieza Completada</h1>
    
    <div class="message">
        Se han eliminado los productos duplicados. Ahora tienes un solo producto de cada tipo.
    </div>
    
    <div class="stats">
        <div class="stat-item">
            <i class="fas fa-trash"></i> <?php echo $eliminados; ?>
        </div>
        <div class="stat-label">Productos duplicados eliminados</div>
        
        <div class="stat-item" style="margin-top: 15px;">
            <i class="fas fa-box"></i> <?php 
            $sql_total = "SELECT COUNT(*) as total FROM productos";
            $res_total = $conexion->query($sql_total);
            $total = $res_total->fetch_assoc()['total'];
            echo $total;
            ?>
        </div>
        <div class="stat-label">Productos únicos en tu tienda</div>
    </div>
    
    <div class="btn-group">
        <a href="index.php" class="btn">
            <i class="fas fa-store"></i> Ir a la Tienda
        </a>
        <a href="admin_panel.php" class="btn btn-secondary">
            <i class="fas fa-cogs"></i> Panel Admin
        </a>
    </div>
</div>

</body>
</html>

<?php
$conexion->close();
?>
