<?php
session_start();
include("conexion.php");

// Verificar si el usuario está autenticado
if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar si es el administrador autorizado
if($_SESSION['usuario_email'] !== 'admin@maximaonline.store') {
    header("Location: index.php");
    exit();
}

// Obtener todos los productos
$sql = "SELECT * FROM productos ORDER BY nombre_producto";
$resultado = $conexion->query($sql);

if(isset($_POST['actualizar_imagen'])) {
    $id_producto = $conexion->real_escape_string($_POST['id_producto']);
    $imagen_url = $conexion->real_escape_string($_POST['imagen_url']);
    
    $sql_update = "UPDATE productos SET imagen = '$imagen_url' WHERE idproductos = $id_producto";
    
    if($conexion->query($sql_update)) {
        $mensaje_exito = "Imagen actualizada exitosamente ✓";
    } else {
        $mensaje_error = "Error al actualizar: " . $conexion->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Actualizar Imágenes - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
    }
    
    .header-actualizar {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .header-actualizar h2 {
        color: #000000;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .header-actualizar p {
        color: #666;
        margin: 0;
    }
    
    .volver-btn {
        display: inline-block;
        margin-bottom: 20px;
        padding: 10px 20px;
        background: #000000;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s;
    }
    
    .volver-btn:hover {
        background: #004080;
    }
    
    .mensaje {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .mensaje.exito {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .mensaje.error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .productos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .producto-card-img {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: all 0.3s;
    }
    
    .producto-card-img:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        transform: translateY(-3px);
    }
    
    .producto-header {
        padding: 15px;
        background: #f9f9f9;
        border-bottom: 1px solid #eee;
    }
    
    .producto-header h4 {
        margin: 0 0 5px 0;
        color: #000000;
        font-size: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .producto-id {
        font-size: 12px;
        color: #999;
        margin: 0;
    }
    
    .preview-img {
        width: 100%;
        height: 280px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ccc;
        font-size: 60px;
        overflow: hidden;
    }
    
    .preview-img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 10px;
        background: white;
    }
    
    .producto-form {
        padding: 15px;
    }
    
    .form-group {
        margin-bottom: 12px;
    }
    
    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    
    .form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 12px;
        box-sizing: border-box;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #ff5500;
        box-shadow: 0 0 5px rgba(255,165,0,0.3);
    }
    
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 11px;
        min-height: 60px;
        font-family: monospace;
        box-sizing: border-box;
        resize: vertical;
    }
    
    .form-group textarea:focus {
        outline: none;
        border-color: #ff5500;
        box-shadow: 0 0 5px rgba(255,165,0,0.3);
    }
    
    .btn-actualizar-img {
        width: 100%;
        padding: 10px;
        background: #ff5500;
        color: #000000;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 13px;
    }
    
    .btn-actualizar-img:hover {
        background: #ff3d00;
        transform: translateY(-2px);
    }
    
    .sin-imagen-text {
        font-size: 12px;
        color: #999;
        text-align: center;
        padding: 40px 10px;
    }
</style>
</head>

<body>

<div class="container">
    <a href="index.php" class="volver-btn">
        <i class="fas fa-arrow-left"></i> Volver a la tienda
    </a>
    
    <div class="header-actualizar">
        <h2>
            <i class="fas fa-images"></i> Actualizar Imágenes de Productos
        </h2>
        <p>Agrega o actualiza el link de la imagen para cada producto. Las imágenes se mostrarán automáticamente en la tienda.</p>
    </div>
    
    <?php if(isset($mensaje_exito)): ?>
        <div class="mensaje exito">
            <i class="fas fa-check-circle"></i>
            <?php echo $mensaje_exito; ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($mensaje_error)): ?>
        <div class="mensaje error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $mensaje_error; ?>
        </div>
    <?php endif; ?>
    
    <div class="productos-grid">
        <?php while($fila = $resultado->fetch_assoc()): ?>
        
        <div class="producto-card-img">
            <div class="producto-header">
                <h4><?php echo $fila['nombre_producto']; ?></h4>
                <p class="producto-id">ID: <?php echo $fila['idproductos']; ?> | Categoría: <?php echo $fila['categoria']; ?></p>
            </div>
            
            <div class="preview-img">
                <?php if(!empty($fila['imagen'])): ?>
                    <img src="<?php echo htmlspecialchars($fila['imagen']); ?>" alt="<?php echo htmlspecialchars($fila['nombre_producto']); ?>" style="object-fit: cover;" onerror="this.parentElement.innerHTML='<i class=\"fas fa-image\"></i>'">
                <?php else: ?>
                    <i class="fas fa-image"></i>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="producto-form">
                <input type="hidden" name="id_producto" value="<?php echo $fila['idproductos']; ?>">
                
                <div class="form-group">
                    <label for="imagen_<?php echo $fila['idproductos']; ?>">Link de la Imagen</label>
                    <textarea id="imagen_<?php echo $fila['idproductos']; ?>" name="imagen_url" placeholder="Pega aquí la URL de la imagen..."><?php echo htmlspecialchars($fila['imagen']); ?></textarea>
                </div>
                
                <button type="submit" name="actualizar_imagen" class="btn-actualizar-img">
                    <i class="fas fa-save"></i> Guardar Imagen
                </button>
            </form>
        </div>
        
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
