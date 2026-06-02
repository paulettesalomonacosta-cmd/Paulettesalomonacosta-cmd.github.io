<?php
session_start();
include("conexion.php");

// Archivo JSON para guardar los datos del carrusel
$carrusel_file = 'carrusel_config.json';

// Leer configuración actual
$carrusel_data = [
    [
        'titulo' => 'DESCUENTO ESPECIAL',
        'descripcion' => 'Hasta 50% en Productos Smart',
        'etiqueta' => 'OFERTA LIMITADA',
        'imagen' => 'https://via.placeholder.com/500x250?text=Ofertas+Smart',
        'gradiente' => 'linear-gradient(135deg, #000000 0%, #ff5500 100%)'
    ],
    [
        'titulo' => 'TECNOLOGÍA AL MEJOR PRECIO',
        'descripcion' => 'Equipos y accesorios con descuento',
        'etiqueta' => 'HOY SOLAMENTE',
        'imagen' => 'https://via.placeholder.com/500x250?text=Tecnologia',
        'gradiente' => 'linear-gradient(135deg, #000000 0%, #ff5500 100%)'
    ],
    [
        'titulo' => 'HOGAR INTELIGENTE',
        'descripcion' => 'Convierte tu hogar con los mejores gadgets',
        'etiqueta' => 'ENVÍO GRATIS',
        'imagen' => 'https://via.placeholder.com/500x250?text=Hogar',
        'gradiente' => 'linear-gradient(135deg, #000000 0%, #004080 100%)'
    ],
    [
        'titulo' => 'MODA Y DEPORTES',
        'descripcion' => 'Colecciones exclusivas con grandes descuentos',
        'etiqueta' => 'REBAJAS',
        'imagen' => 'https://via.placeholder.com/500x250?text=Moda',
        'gradiente' => 'linear-gradient(135deg, #1e4d8b 0%, #ff5500 100%)'
    ],
    [
        'titulo' => 'PASTAMANÍAS GOURMET',
        'descripcion' => 'Sabores italianos auténticos importados',
        'etiqueta' => 'CON REGALO',
        'imagen' => 'https://via.placeholder.com/500x250?text=Gastronomia',
        'gradiente' => 'linear-gradient(135deg, #000000 0%, #000000 100%)'
    ]
];

// Si existe archivo JSON, cargar desde allí
if(file_exists($carrusel_file)) {
    $json_data = file_get_contents($carrusel_file);
    $carrusel_data = json_decode($json_data, true);
}

// Procesar formulario
$mensaje = '';
if(isset($_POST['guardar_carrusel'])) {
    $nuevos_datos = [];
    
    for($i = 0; $i < 5; $i++) {
        $nuevos_datos[] = [
            'titulo' => isset($_POST["titulo_$i"]) ? $conexion->real_escape_string($_POST["titulo_$i"]) : $carrusel_data[$i]['titulo'],
            'descripcion' => isset($_POST["descripcion_$i"]) ? $conexion->real_escape_string($_POST["descripcion_$i"]) : $carrusel_data[$i]['descripcion'],
            'etiqueta' => isset($_POST["etiqueta_$i"]) ? $conexion->real_escape_string($_POST["etiqueta_$i"]) : $carrusel_data[$i]['etiqueta'],
            'imagen' => isset($_POST["imagen_$i"]) ? $conexion->real_escape_string($_POST["imagen_$i"]) : $carrusel_data[$i]['imagen'],
            'gradiente' => isset($_POST["gradiente_$i"]) ? $conexion->real_escape_string($_POST["gradiente_$i"]) : $carrusel_data[$i]['gradiente']
        ];
    }
    
    // Guardar en JSON
    if(file_put_contents($carrusel_file, json_encode($nuevos_datos, JSON_PRETTY_PRINT))) {
        $mensaje = '<div style="background: #4caf50; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">✓ ¡Carrusel actualizado correctamente!</div>';
        $carrusel_data = $nuevos_datos;
    } else {
        $mensaje = '<div style="background: #f44336; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">✗ Error al guardar los cambios</div>';
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configurar Carrusel - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Arial', sans-serif;
        padding: 40px 20px;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .header {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        margin-bottom: 30px;
    }

    .header h1 {
        color: #000000;
        margin-bottom: 10px;
        font-size: 28px;
    }

    .header p {
        color: #666;
        font-size: 14px;
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

    .form-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        padding: 30px;
    }

    .slide-form {
        background: #f9f9f9;
        padding: 25px;
        margin-bottom: 20px;
        border-radius: 10px;
        border-left: 5px solid #ff6b6b;
    }

    .slide-form h3 {
        color: #000000;
        margin-bottom: 20px;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .slide-form h3 i {
        font-size: 24px;
        color: #ff6b6b;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: bold;
        font-size: 14px;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        font-family: Arial, sans-serif;
        transition: all 0.3s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ff6b6b;
        box-shadow: 0 0 8px rgba(255, 107, 107, 0.3);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .preview-box {
        background: white;
        padding: 15px;
        border-radius: 5px;
        margin-top: 15px;
        border: 2px dashed #ff6b6b;
    }

    .preview-box p {
        margin: 8px 0;
        color: #666;
        font-size: 13px;
    }

    .preview-box strong {
        color: #000000;
    }

    .preview-img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 5px;
        margin-top: 10px;
    }

    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        justify-content: center;
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-guardar {
        background: #4caf50;
        color: white;
    }

    .btn-guardar:hover {
        background: #45a049;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
    }

    .btn-restablecer {
        background: #f44336;
        color: white;
    }

    .btn-restablecer:hover {
        background: #da190b;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(244, 67, 54, 0.3);
    }

    .info-box {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 25px;
        color: #1565c0;
        font-size: 13px;
        line-height: 1.6;
    }

    .info-box strong {
        display: block;
        margin-bottom: 8px;
    }

    @media (max-width: 768px) {
        .slide-form {
            padding: 15px;
        }

        .button-group {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>
</head>

<body>

<div class="container">
    <a href="admin_panel.php" class="volver-btn">
        <i class="fas fa-arrow-left"></i> Volver al Panel
    </a>

    <div class="header">
        <h1><i class="fas fa-images"></i> Configurar Carrusel de Ofertas</h1>
        <p>Edita fácilmente las 5 imágenes y textos del carrusel de inicio</p>
    </div>

    <div class="form-container">
        <?php echo $mensaje; ?>

        <div class="info-box">
            <strong>📌 ¿Cómo usar?</strong>
            Solo necesitas pegar el link (URL) de tus imágenes. Las imágenes se adaptarán automáticamente al tamaño del carrusel (500x250px recomendado).
            <br><strong>Puedes obtener links de imágenes en:</strong> Pixabay, Pexels, Unsplash, Amazon, o cualquier sitio web.
        </div>

        <form method="POST" action="">
            <?php for($i = 0; $i < 5; $i++): ?>
                <div class="slide-form">
                    <h3>
                        <i class="fas fa-image"></i>
                        Slide <?php echo ($i + 1); ?>
                    </h3>

                    <div class="form-group">
                        <label for="titulo_<?php echo $i; ?>">Título:</label>
                        <input type="text" name="titulo_<?php echo $i; ?>" id="titulo_<?php echo $i; ?>" 
                               value="<?php echo htmlspecialchars($carrusel_data[$i]['titulo']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_<?php echo $i; ?>">Descripción:</label>
                        <textarea name="descripcion_<?php echo $i; ?>" id="descripcion_<?php echo $i; ?>" required><?php echo htmlspecialchars($carrusel_data[$i]['descripcion']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="etiqueta_<?php echo $i; ?>">Etiqueta (Badge):</label>
                        <input type="text" name="etiqueta_<?php echo $i; ?>" id="etiqueta_<?php echo $i; ?>" 
                               value="<?php echo htmlspecialchars($carrusel_data[$i]['etiqueta']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="imagen_<?php echo $i; ?>">
                            <i class="fas fa-link"></i> Link de la Imagen (URL):
                        </label>
                        <input type="url" name="imagen_<?php echo $i; ?>" id="imagen_<?php echo $i; ?>" 
                               value="<?php echo htmlspecialchars($carrusel_data[$i]['imagen']); ?>" 
                               placeholder="https://ejemplo.com/imagen.jpg" required>
                        
                        <div class="preview-box">
                            <p><strong>Vista previa:</strong></p>
                            <img src="<?php echo htmlspecialchars($carrusel_data[$i]['imagen']); ?>" 
                                 alt="Preview" class="preview-img" onerror="this.src='https://via.placeholder.com/500x250?text=Error'">
                            <p style="margin-top: 10px; font-size: 12px; color: #999;">
                                <i class="fas fa-info-circle"></i> La imagen se mostrará aquí cuando guardes
                            </p>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

            <div class="button-group">
                <button type="submit" name="guardar_carrusel" class="btn btn-guardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <button type="reset" class="btn btn-restablecer">
                    <i class="fas fa-undo"></i> Limpiar Formulario
                </button>
            </div>
        </form>
    </div>

</div>

</body>
</html>
