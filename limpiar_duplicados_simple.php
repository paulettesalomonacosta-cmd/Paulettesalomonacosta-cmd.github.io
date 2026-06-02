<?php
include("conexion.php");

// Verificar si hay duplicados
$sql_check = "SELECT nombre_producto, COUNT(*) as cantidad FROM productos GROUP BY nombre_producto HAVING cantidad > 1 ORDER BY cantidad DESC";
$resultado_check = $conexion->query($sql_check);
$duplicados = [];
while($fila = $resultado_check->fetch_assoc()) {
    $duplicados[] = $fila;
}

// Si hay duplicados, limpiarlos
$eliminados = 0;
if(count($duplicados) > 0) {
    $conexion->query("SET FOREIGN_KEY_CHECKS=0");
    
    foreach($duplicados as $producto) {
        $nombre = $conexion->real_escape_string($producto['nombre_producto']);
        
        // Obtener todos los IDs del producto
        $sql_ids = "SELECT idproductos FROM productos WHERE nombre_producto = '$nombre' ORDER BY idproductos ASC";
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
                $eliminados += count($ids_a_eliminar);
            }
        }
    }
    
    $conexion->query("SET FOREIGN_KEY_CHECKS=1");
}

// Contar productos únicos después de la limpieza
$sql_total = "SELECT COUNT(*) as total FROM productos";
$resultado_total = $conexion->query($sql_total);
$total = $resultado_total->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpieza de Duplicados - MAXIMA ONLINE STORE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px 40px;
            max-width: 600px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 30px;
            animation: popIn 0.6s ease-out;
        }
        
        @keyframes popIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        h1 {
            color: #000000;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            color: #ff5500;
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        .message {
            font-size: 16px;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.8;
        }
        
        .stats {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .stat-item {
            flex: 1;
            min-width: 150px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 40px;
            font-weight: bold;
            color: #ff5500;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #000000 0%, #004080 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,43,91,0.3);
        }
        
        .btn-secondary {
            background: #ff5500;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #ff3d00;
            transform: translateY(-2px);
        }
        
        .alert {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            text-align: left;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>¡Limpieza Completada! ✓</h1>
        <p class="subtitle">Tus productos están organizados</p>
        
        <?php if($eliminados > 0): ?>
            <div class="alert">
                <i class="fas fa-info-circle"></i> Se eliminaron <?php echo $eliminados; ?> producto(s) duplicado(s) de la base de datos.
            </div>
        <?php endif; ?>
        
        <p class="message">
            <?php 
                if($eliminados > 0) {
                    echo "Se ha completado la limpieza de duplicados. Ahora tu tienda tiene productos únicos sin repeticiones.";
                } else {
                    echo "Ya no hay productos duplicados en tu tienda. ¡Todo está limpio y ordenado!";
                }
            ?>
        </p>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $eliminados; ?></div>
                <div class="stat-label">Duplicados eliminados</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label">Productos únicos</div>
            </div>
        </div>
        
        <div class="buttons">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Ir a la tienda
            </a>
            <a href="admin_panel.php" class="btn btn-secondary">
                <i class="fas fa-cog"></i> Panel de Admin
            </a>
        </div>
    </div>
</body>
</html>
