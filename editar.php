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

$id = $_GET['id'];

$sql = "SELECT * FROM productos WHERE idproductos='$id'";
$resultado = $conexion->query($sql);

$fila = $resultado->fetch_assoc();

if(isset($_POST['actualizar'])){
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    $precio = $conexion->real_escape_string($_POST['precio']);
    $stock = $conexion->real_escape_string($_POST['stock']);
    $imagen = $conexion->real_escape_string($_POST['imagen']);
    $categoria = $conexion->real_escape_string($_POST['categoria']);
    $calificacion = $conexion->real_escape_string($_POST['calificacion']);
    $descuento = $conexion->real_escape_string($_POST['descuento']);

    $sql2 = "UPDATE productos SET
    nombre_producto='$nombre',
    descripcion='$descripcion',
    precio=$precio,
    stock=$stock,
    imagen='$imagen',
    categoria='$categoria',
    calificacion=$calificacion,
    descuento=$descuento
    WHERE idproductos='$id'";

    if($conexion->query($sql2)) {
        header("Location:index.php");
    } else {
        $error = "Error al actualizar: " . $conexion->error;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Producto - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .form-container {
        max-width: 600px;
        margin: 30px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-container h2 {
        color: #000000;
        margin-bottom: 20px;
        text-align: center;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: bold;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        font-family: Arial;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #ff5500;
        box-shadow: 0 0 5px rgba(255,165,0,0.3);
    }
    .form-buttons {
        display: flex;
        gap: 10px;
    }
    .btn-actualizar, .btn-cancelar {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }
    .btn-actualizar {
        background: #28a745;
        color: white;
    }
    .btn-actualizar:hover {
        background: #218838;
    }
    .btn-cancelar {
        background: #666;
        color: white;
    }
    .btn-cancelar:hover {
        background: #555;
    }
    .error {
        background: #fee;
        color: #c00;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        border-left: 4px solid #c00;
    }
    .info-link {
        background: #e3f2fd;
        color: #1976d2;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        border-left: 4px solid #1976d2;
        font-size: 13px;
    }
    .preview-imagen {
        margin-bottom: 15px;
        text-align: center;
        padding: 15px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 8px;
    }
    .preview-imagen img {
        max-width: 250px;
        max-height: 250px;
        border-radius: 5px;
        background: white;
        padding: 10px;
        object-fit: contain;
    }
</style>
</head>

<body>

<div class="form-container">
    <a href="index.php" style="display: inline-block; margin-bottom: 20px; color: #000000; text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    
    <h2><i class="fas fa-edit"></i> Editar Producto</h2>
    
    <?php if(isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="nombre">Nombre del Producto *</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $fila['nombre_producto']; ?>" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción *</label>
            <textarea id="descripcion" name="descripcion" required><?php echo $fila['descripcion']; ?></textarea>
        </div>

        <div class="form-group">
            <label for="categoria">Categoría *</label>
            <select id="categoria" name="categoria" required>
                <option value="Productos smart" <?php echo ($fila['categoria'] === 'Productos smart') ? 'selected' : ''; ?>>Productos smart</option>
                <option value="Coventra smart" <?php echo ($fila['categoria'] === 'Coventra smart') ? 'selected' : ''; ?>>Coventra smart</option>
                <option value="Novedades" <?php echo ($fila['categoria'] === 'Novedades') ? 'selected' : ''; ?>>Novedades</option>
                <option value="Tecnología" <?php echo ($fila['categoria'] === 'Tecnología') ? 'selected' : ''; ?>>Tecnología</option>
                <option value="Hogar" <?php echo ($fila['categoria'] === 'Hogar') ? 'selected' : ''; ?>>Hogar</option>
                <option value="Moda" <?php echo ($fila['categoria'] === 'Moda') ? 'selected' : ''; ?>>Moda</option>
                <option value="Deportes" <?php echo ($fila['categoria'] === 'Deportes') ? 'selected' : ''; ?>>Deportes</option>
                <option value="Pastamanías" <?php echo ($fila['categoria'] === 'Pastamanías') ? 'selected' : ''; ?>>Pastamanías</option>
            </select>
        </div>

        <div class="form-group">
            <label for="precio">Precio ($) *</label>
            <input type="number" id="precio" name="precio" step="0.01" value="<?php echo $fila['precio']; ?>" required>
        </div>

        <div class="form-group">
            <label for="stock">Stock (cantidad) *</label>
            <input type="number" id="stock" name="stock" value="<?php echo $fila['stock']; ?>" required>
        </div>

        <div class="form-group">
            <label for="calificacion">Calificación (1-5) *</label>
            <input type="number" id="calificacion" name="calificacion" min="1" max="5" step="0.1" value="<?php echo $fila['calificacion']; ?>" required>
        </div>

        <div class="form-group">
            <label for="descuento">Descuento (%) *</label>
            <input type="number" id="descuento" name="descuento" min="0" max="100" value="<?php echo $fila['descuento']; ?>" required>
        </div>

        <div class="form-group">
            <label for="imagen">Link de la Imagen *</label>
            <div class="info-link">
                <i class="fas fa-info-circle"></i> Pega aquí la URL completa de la imagen (ej: https://example.com/imagen.jpg)
            </div>
            <input type="url" id="imagen" name="imagen" placeholder="https://ejemplo.com/imagen.jpg" value="<?php echo htmlspecialchars($fila['imagen']); ?>" required>
            <?php if(!empty($fila['imagen'])): ?>
                <div class="preview-imagen">
                    <img src="<?php echo htmlspecialchars($fila['imagen']); ?>" alt="Preview" onerror="this.style.display='none'">
                </div>
            <?php endif; ?>
        </div>

        <div class="form-buttons">
            <button type="submit" name="actualizar" class="btn-actualizar">
                <i class="fas fa-check"></i> Actualizar Producto
            </button>
            <a href="index.php" style="flex: 1;">
                <button type="button" class="btn-cancelar" style="width: 100%;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </a>
        </div>
    </form>
</div>

</body>
</html>
