<?php
session_start();

if(!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = array();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Lista de Deseos - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .wishlist-container {
        max-width: 1000px;
        margin: 30px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .wishlist-container h2 {
        color: #000000;
        margin-bottom: 20px;
    }
    .wishlist-vacia {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .wishlist-vacia i {
        font-size: 60px;
        color: #ddd;
        margin-bottom: 20px;
    }
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    .wishlist-card {
        background: #f9f9f9;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .wishlist-card img {
        width: 100%;
        height: 220px;
        object-fit: contain;
        background: white;
        padding: 10px;
    }
    .wishlist-card-info {
        padding: 15px;
    }
    .wishlist-card-info h4 {
        margin: 0 0 10px 0;
        color: #000000;
    }
    .wishlist-card-price {
        color: #ff5500;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .wishlist-card-buttons {
        display: flex;
        gap: 10px;
    }
    .wishlist-card-buttons button {
        flex: 1;
        padding: 8px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 13px;
        font-weight: bold;
        transition: all 0.3s;
    }
    .btn-carrito-ws {
        background: #000000;
        color: white;
    }
    .btn-carrito-ws:hover {
        background: #004080;
    }
    .btn-eliminar-ws {
        background: #dc3545;
        color: white;
    }
    .btn-eliminar-ws:hover {
        background: #c82333;
    }
</style>
</head>
<body>

<div class="wishlist-container">
    <a href="index.php" style="display: inline-block; margin-bottom: 20px; color: #000000; text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Volver a la tienda
    </a>
    
    <h2><i class="fas fa-heart"></i> Mi Lista de Deseos</h2>
    
    <?php 
    if(empty($_SESSION['wishlist'])): 
    ?>
        <div class="wishlist-vacia">
            <i class="fas fa-heart"></i>
            <h3>Tu lista de deseos está vacía</h3>
            <p>Agrega productos que te gusten</p>
        </div>
    <?php 
    else: 
    ?>
        <div class="wishlist-grid">
            <?php 
            include("conexion.php");
            foreach($_SESSION['wishlist'] as $id):
                $sql = "SELECT * FROM productos WHERE idproductos = $id";
                $resultado = $conexion->query($sql);
                
                if($resultado->num_rows > 0):
                    $producto = $resultado->fetch_assoc();
            ?>
                <div class="wishlist-card">
                    <?php if(!empty($producto['imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" onerror="this.src='https://via.placeholder.com/250x220?text=Sin+imagen'">
                    <?php else: ?>
                        <div style="width: 100%; height: 220px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-image" style="font-size: 60px; color: #ddd;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="wishlist-card-info">
                        <h4><?php echo substr($producto['nombre_producto'], 0, 30); ?></h4>
                        <div class="wishlist-card-price">$<?php echo number_format($producto['precio'], 2); ?></div>
                        <div class="wishlist-card-buttons">
                            <a href="agregar_carrito.php?id=<?php echo $producto['idproductos']; ?>" style="flex: 1;">
                                <button class="btn-carrito-ws"><i class="fas fa-shopping-cart"></i> Agregar</button>
                            </a>
                            <a href="eliminar_wishlist.php?id=<?php echo $producto['idproductos']; ?>" style="flex: 1;">
                                <button class="btn-eliminar-ws"><i class="fas fa-trash"></i> Eliminar</button>
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
