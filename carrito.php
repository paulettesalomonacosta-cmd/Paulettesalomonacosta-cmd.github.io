<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

$total = 0;
$cantidad_total = 0;
$carrito_mayor_100 = false;
if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $it) {
        if ($it['precio'] >= 100) { $carrito_mayor_100 = true; break; }
    }
}
$compra_requerida = !empty($_SESSION['compra_requerida']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Carrito - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .carrito-container {
        max-width: 1000px;
        margin: 30px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .carrito-container h2 {
        color: #000000;
        margin-bottom: 20px;
    }
    .carrito-vacio {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .carrito-vacio i {
        font-size: 60px;
        color: #ddd;
        margin-bottom: 20px;
    }
    .carrito-tabla {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .carrito-tabla th {
        background: #000000;
        color: white;
        padding: 15px;
        text-align: left;
    }
    .carrito-tabla td {
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }
    .carrito-tabla tr:hover {
        background: #f9f9f9;
    }
    .producto-img-carrito {
        width: 80px;
        height: 80px;
        object-fit: contain;
        border-radius: 5px;
        background: white;
        padding: 5px;
    }
    .btn-eliminar {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
    }
    .btn-eliminar:hover {
        background: #c82333;
    }
    .carrito-total {
        text-align: right;
        font-size: 20px;
        margin-bottom: 20px;
    }
    .carrito-total strong {
        color: #000000;
    }
    .carrito-acciones {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .btn-continuar, .btn-comprar {
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }
    .btn-continuar {
        background: #666;
        color: white;
    }
    .btn-continuar:hover {
        background: #555;
    }
    .btn-comprar {
        background: #28a745;
        color: white;
    }
    .btn-comprar:hover {
        background: #218838;
    }
</style>
</head>
<body>

<div class="carrito-container">
    <a href="index.php" style="display: inline-block; margin-bottom: 20px; color: #000000; text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Volver a la tienda
    </a>
    
    <h2><i class="fas fa-shopping-cart"></i> Mi Carrito de Compras</h2>
    
    <?php 
    if(empty($_SESSION['carrito'])): 
    ?>
        <div class="carrito-vacio">
            <i class="fas fa-shopping-cart"></i>
            <h3>Tu carrito está vacío</h3>
            <p>Agrega productos para comenzar a comprar</p>
            <a href="index.php" style="display: inline-block; margin-top: 20px; padding: 12px 30px; background: #000000; color: white; text-decoration: none; border-radius: 5px;">
                Continuar Comprando
            </a>
        </div>
    <?php 
    else: 
    ?>
        <table class="carrito-tabla">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach($_SESSION['carrito'] as $id => $item):
                    $subtotal = $item['precio'] * $item['cantidad'];
                    $total += $subtotal;
                    $cantidad_total += $item['cantidad'];
                ?>
                <tr>
                    <td>
                        <?php if(!empty($item['imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>" class="producto-img-carrito">
                        <?php else: ?>
                            <i class="fas fa-image" style="font-size: 40px; color: #ddd;"></i>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['nombre']; ?></td>
                    <td>$<?php echo number_format($item['precio'], 2); ?></td>
                    <td><?php echo $item['cantidad']; ?></td>
                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                    <td>
                        <a href="eliminar_carrito.php?id=<?php echo $id; ?>">
                            <button class="btn-eliminar"><i class="fas fa-trash"></i> Eliminar</button>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="carrito-total">
            Total: <strong>$<?php echo number_format($total, 2); ?></strong>
        </div>
        <?php if ($compra_requerida): ?>
            <div class="carrito-acciones" style="flex-direction:column; gap:12px;">
                <div class="alerta warning" style="background:#fff4e5; border-color:#ffb236; color:#7a4a04; padding:12px; border-radius:8px;">
                    <i class="fas fa-exclamation-circle"></i> 🎮 Ya jugaste MaxArena. Debes hacer una compra mínima de $100 para poder jugar de nuevo. Completa esta compra primero.
                </div>
                <div style="display:flex; gap:10px;">
                    <a href="index.php"><button class="btn-continuar"><i class="fas fa-shopping-bag"></i> Continuar Comprando</button></a>
                    <a href="seleccionar_direccion.php"><button class="btn-comprar"><i class="fas fa-map-marker-alt"></i> Seleccionar Dirección de Entrega</button></a>
                </div>
            </div>
        <?php else: ?>
            <div class="carrito-acciones">
                <a href="index.php">
                    <button class="btn-continuar"><i class="fas fa-shopping-bag"></i> Continuar Comprando</button>
                </a>
                <a href="seleccionar_direccion.php">
                    <button class="btn-comprar"><i class="fas fa-map-marker-alt"></i> Seleccionar Dirección de Entrega</button>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
