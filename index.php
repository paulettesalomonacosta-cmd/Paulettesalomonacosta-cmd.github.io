<?php
session_start();
include("conexion.php");

// Obtener la categoría seleccionada (si existe)
$categoria_filtro = isset($_GET['categoria']) ? $conexion->real_escape_string($_GET['categoria']) : '';
$busqueda = isset($_GET['buscar']) ? $conexion->real_escape_string($_GET['buscar']) : '';
$ofertas = isset($_GET['ofertas']) ? true : false;
$precio_min = isset($_GET['precio_min']) ? floatval($_GET['precio_min']) : 0;
$precio_max = isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : 1500;
$marcas_seleccionadas = [];
if(isset($_GET['marca']) && is_array($_GET['marca'])) {
    foreach($_GET['marca'] as $marca) {
        $marca = $conexion->real_escape_string($marca);
        if($marca !== '') {
            $marcas_seleccionadas[] = $marca;
        }
    }
}

// Construir la consulta con filtro de categoría, búsqueda, precio y marca
$condiciones = [];
if($busqueda) {
    $condiciones[] = "nombre_producto LIKE '%$busqueda%'";
    $titulo_categoria = "Resultados de búsqueda: " . htmlspecialchars($busqueda);
} elseif($ofertas && $categoria_filtro) {
    $condiciones[] = "descuento > 0";
    $condiciones[] = "categoria = '$categoria_filtro'";
    $titulo_categoria = "🏷️ OFERTAS - " . ucfirst($categoria_filtro);
} elseif($ofertas) {
    $condiciones[] = "descuento > 0";
    $titulo_categoria = "🏷️ OFERTAS Y DESCUENTOS";
} elseif($categoria_filtro) {
    $condiciones[] = "categoria = '$categoria_filtro'";
    $titulo_categoria = ucfirst($categoria_filtro);
} else {
    $titulo_categoria = "TODOS LOS PRODUCTOS";
}

if($precio_min > 0) {
    $condiciones[] = "precio >= $precio_min";
}
if($precio_max > 0 && $precio_max >= $precio_min) {
    $condiciones[] = "precio <= $precio_max";
}

if(!empty($marcas_seleccionadas)) {
    $marca_clauses = [];
    foreach($marcas_seleccionadas as $marcaItem) {
        $marca_clauses[] = "nombre_producto LIKE '%$marcaItem%'";
    }
    if(!empty($marca_clauses)) {
        $condiciones[] = '(' . implode(' OR ', $marca_clauses) . ')';
    }
}

$sql = "SELECT DISTINCT * FROM productos";
if(!empty($condiciones)) {
    $sql .= ' WHERE ' . implode(' AND ', $condiciones);
}
$sql .= " ORDER BY idproductos ASC";

if($busqueda) {
    $sql .= " LIMIT 100";
}

$resultado = $conexion->query($sql);

// Contar items en carrito y wishlist
$carrito_count = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;
$wishlist_count = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
// Datos para el mini-juego
$carrito_total = 0;
$carrito_mayor_100 = false;
if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $it) {
        $carrito_total += $it['precio'] * $it['cantidad'];
        if ($it['precio'] >= 100) $carrito_mayor_100 = true;
    }
}

// Cargar configuración del carrusel
$carrusel_file = 'carrusel_config.json';
$carrusel_data = [];

if(file_exists($carrusel_file)) {
    $json_data = file_get_contents($carrusel_file);
    $carrusel_data = json_decode($json_data, true);
} else {
    // Datos por defecto si no existe archivo
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
            'gradiente' => 'linear-gradient(135deg, #000000 0%, #ff3d00 100%)'
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
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MAXIMA ONLINE STORE - Tienda Online</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    /* ROBOT MASCOTA 3D PROFESIONAL ESTILO ALTO NIVEL */
    .robot-mascota {
        position: relative;
        width: 160px;
        height: 240px;
        cursor: pointer;
        user-select: none;
        z-index: 100;
        margin: 20px auto;
        left: -60px;
        perspective: 1200px;
    }

    .robot-mascota.activo {
        animation: robotActivo 0.4s ease-out forwards !important;
    }

    @keyframes robotActivo {
        0% { transform: scale(1); }
        50% { transform: scale(1.08); }
        100% { transform: scale(1); }
    }

    .robot-contenedor {
        position: relative;
        width: 160px;
        height: 240px;
        transform-style: preserve-3d;
        animation: flotarRobot3d 4s ease-in-out infinite;
    }

    @keyframes flotarRobot3d {
        0%, 100% { 
            transform: translateY(0px) rotateX(0deg) rotateY(8deg) rotateZ(0deg);
        }
        25% { 
            transform: translateY(-15px) rotateX(-3deg) rotateY(-5deg) rotateZ(-2deg);
        }
        50% { 
            transform: translateY(-25px) rotateX(0deg) rotateY(0deg) rotateZ(0deg);
        }
        75% { 
            transform: translateY(-15px) rotateX(3deg) rotateY(5deg) rotateZ(2deg);
        }
    }

    /* ANTENA NARANJA BRILLANTE */
    .robot-antena-top {
        position: absolute;
        top: -35px;
        left: 50%;
        transform: translateX(-50%);
        width: 14px;
        height: 40px;
        border-radius: 50% 50% 35% 35%;
        background: linear-gradient(90deg, #2a2a2a, #0d0d0d, #2a2a2a);
        box-shadow: 
            0 0 15px rgba(100, 200, 255, 0.3),
            0 6px 12px rgba(0,0,0,0.4);
    }

    .robot-antena-bola {
        position: absolute;
        top: -28px;
        left: 50%;
        transform: translateX(-50%);
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: radial-gradient(circle at 35% 35%, #ffcc33, #ff9900, #ff6600);
        box-shadow: 
            0 0 35px rgba(255, 153, 0, 1),
            0 0 20px rgba(255, 200, 50, 0.6),
            inset -4px -4px 10px rgba(0,0,0,0.4),
            0 6px 15px rgba(0,0,0,0.5);
        animation: antenar 2s ease-in-out infinite;
    }

    @keyframes antenar {
        0%, 100% { transform: translateX(-50%) scale(1); }
        50% { transform: translateX(-50%) scale(1.1); }
    }

    /* CABEZA GRANDE Y REDONDEADA */
    .robot-cabeza {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 155px;
        height: 135px;
        background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 30%, #e0e0e0 70%, #d0d0d0 100%);
        border-radius: 45px;
        border: 6px solid #2a2a2a;
        box-shadow: 
            0 30px 60px rgba(0,0,0,0.35),
            inset -8px -8px 20px rgba(0,0,0,0.12),
            inset 8px 8px 20px rgba(255,255,255,0.6);
        transform-style: preserve-3d;
    }

    /* AURICULARES NARANJAS Y NEGROS */
    .robot-auricular {
        position: absolute;
        top: 25px;
        width: 32px;
        height: 60px;
        background: linear-gradient(90deg, #0d0d0d, #000000, #0d0d0d);
        border-radius: 50% 50% 35% 35%;
        box-shadow: 
            0 12px 24px rgba(0,0,0,0.5),
            inset -3px 0 10px rgba(100, 200, 255, 0.1);
        border: 3px solid #ff9900;
    }

    .robot-auricular-izq {
        left: -22px;
        transform: perspective(600px) rotateY(25deg);
    }

    .robot-auricular-der {
        right: -22px;
        transform: perspective(600px) rotateY(-25deg);
    }

    /* PANTALLA LED GRANDE CON EXPRESIÓN */
    .robot-pantalla {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 95px;
        height: 70px;
        background: radial-gradient(ellipse at center, #000000, #0a0a0a);
        border-radius: 12px;
        border: 4px solid #000000;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 12px;
        box-shadow: 
            inset 0 0 25px rgba(0,0,0,0.8),
            0 0 30px rgba(100, 200, 255, 0.35),
            inset -3px -3px 10px rgba(100, 200, 255, 0.08);
    }

    .robot-ojos {
        display: flex;
        gap: 18px;
        justify-content: center;
        align-items: center;
    }

    .robot-ojo {
        width: 22px;
        height: 22px;
        background: radial-gradient(circle at 40% 40%, #64f0ff, #00d9ff, #0084d1);
        border-radius: 50%;
        box-shadow: 
            0 0 25px rgba(100, 240, 255, 0.95),
            inset -3px -3px 8px rgba(0,0,0,0.4),
            0 0 15px rgba(0, 211, 255, 0.6);
        animation: parpadeoSonriente 2.5s ease-in-out infinite;
        position: relative;
    }

    .robot-ojo::after {
        content: '';
        position: absolute;
        width: 8px;
        height: 8px;
        background: radial-gradient(circle, #ffffff, #b3e5fc);
        border-radius: 50%;
        top: 4px;
        left: 4px;
        box-shadow: 0 0 8px rgba(255,255,255,0.8);
    }

    .robot-ojo-der {
        animation-delay: 0.4s;
    }

    @keyframes parpadeoSonriente {
        0%, 80%, 100% { 
            opacity: 1;
            transform: scaleY(1);
            box-shadow: 
                0 0 25px rgba(100, 240, 255, 0.95),
                inset -3px -3px 8px rgba(0,0,0,0.4),
                0 0 15px rgba(0, 211, 255, 0.6);
        }
        88% { 
            opacity: 0.15;
            transform: scaleY(0.15);
            box-shadow: 
                0 0 12px rgba(100, 240, 255, 0.5),
                inset -2px -2px 4px rgba(0,0,0,0.2),
                0 0 8px rgba(0, 211, 255, 0.3);
        }
    }

    .robot-boca {
        width: 40px;
        height: 3px;
        background: linear-gradient(90deg, transparent, #00d4ff, #00d4ff, transparent);
        border-radius: 50%;
        box-shadow: 0 0 15px rgba(100, 240, 255, 0.8);
        position: relative;
    }

    .robot-boca::after {
        content: '';
        position: absolute;
        width: 36px;
        height: 14px;
        background: transparent;
        border: 2.5px solid #00d4ff;
        border-top: none;
        border-radius: 0 0 50px 50px;
        left: 2px;
        top: 1px;
        box-shadow: 
            inset 0 2px 8px rgba(0, 212, 255, 0.5),
            0 4px 12px rgba(0, 212, 255, 0.4);
    }

    /* CUERPO CENTRAL PROPORCIONADO */
    .robot-cuerpo {
        position: absolute;
        top: 115px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 95px;
        background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 30%, #e0e0e0 70%, #d0d0d0 100%);
        border: 6px solid #2a2a2a;
        border-radius: 28px;
        box-shadow: 
            0 25px 50px rgba(0,0,0,0.3),
            inset -8px -8px 20px rgba(0,0,0,0.1),
            inset 8px 8px 20px rgba(255,255,255,0.5);
        transform-style: preserve-3d;
    }

    /* PANEL DEL PECHO NARANJA Y NEGRO */
    .robot-pecho {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 65px;
        height: 65px;
        background: radial-gradient(circle at 35% 35%, #ffaa00, #ff9900, #ff7700);
        border: 5px solid #000000;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 
            0 12px 24px rgba(0,0,0,0.35),
            inset -5px -5px 12px rgba(0,0,0,0.25),
            inset 5px 5px 12px rgba(255,255,255,0.3),
            0 0 30px rgba(255, 153, 0, 0.5);
    }

    .robot-carrito {
        font-size: 32px;
        filter: drop-shadow(0 3px 6px rgba(0,0,0,0.4));
    }

    /* BRAZOS NEGROS INTEGRADOS AL CUERPO */
    .robot-brazo {
        position: absolute;
        top: 105px;
        width: 30px;
        height: 68px;
        background: linear-gradient(90deg, #0d0d0d, #000000, #0d0d0d);
        border: 4px solid #2a2a2a;
        border-radius: 50%;
        transform-style: preserve-3d;
        box-shadow: 
            0 14px 28px rgba(0,0,0,0.4),
            inset -3px 0 10px rgba(100, 200, 255, 0.15);
    }

    .robot-brazo-izq {
        left: -24px;
        transform-origin: 70% 0%;
        animation: brazoIzq3dPro 2s ease-in-out infinite;
    }

    .robot-brazo-der {
        right: -24px;
        transform-origin: 30% 0%;
        animation: brazoDer3dPro 2s ease-in-out infinite;
    }

    @keyframes brazoIzq3dPro {
        0% { 
            transform: rotateZ(0deg) rotateY(-8deg);
        }
        40% { 
            transform: rotateZ(-25deg) rotateY(10deg);
        }
        50% { 
            transform: rotateZ(-45deg) rotateY(12deg);
        }
        60% { 
            transform: rotateZ(-35deg) rotateY(11deg);
        }
        100% { 
            transform: rotateZ(0deg) rotateY(-8deg);
        }
    }

    @keyframes brazoDer3dPro {
        0% { 
            transform: rotateZ(0deg) rotateY(8deg);
        }
        40% { 
            transform: rotateZ(25deg) rotateY(-10deg);
        }
        50% { 
            transform: rotateZ(45deg) rotateY(-12deg);
        }
        60% { 
            transform: rotateZ(35deg) rotateY(-11deg);
        }
        100% { 
            transform: rotateZ(0deg) rotateY(8deg);
        }
    }

    /* MANO CON DEDOS ARTICULADOS */
    .robot-mano {
        position: absolute;
        bottom: -18px;
        left: 50%;
        transform: translateX(-50%);
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #000000, #0d0d0d, #000000);
        border: 4px solid #2a2a2a;
        border-radius: 50%;
        box-shadow: 
            0 10px 20px rgba(0,0,0,0.4),
            inset -3px -3px 8px rgba(0,0,0,0.3),
            0 0 15px rgba(255, 153, 0, 0.2);
    }

    /* PIERNAS BLANCAS INTEGRADAS AL CUERPO */
    .robot-pierna {
        position: absolute;
        bottom: -20px;
        width: 30px;
        height: 55px;
        background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 40%, #e0e0e0 70%, #d0d0d0 100%);
        border: 4px solid #2a2a2a;
        border-radius: 50%;
        box-shadow: 
            0 14px 28px rgba(0,0,0,0.3),
            inset -4px -4px 12px rgba(0,0,0,0.08),
            inset 4px 4px 12px rgba(255,255,255,0.4);
    }

    .robot-pierna-izq {
        left: 16px;
        animation: piernaIzq3d 1.8s ease-in-out infinite;
    }

    .robot-pierna-der {
        right: 16px;
        animation: piernaDer3d 1.8s ease-in-out infinite;
    }

    @keyframes piernaIzq3d {
        0%, 100% { transform: rotateZ(0deg); }
        50% { transform: rotateZ(-16deg); }
    }

    @keyframes piernaDer3d {
        0%, 100% { transform: rotateZ(0deg); }
        50% { transform: rotateZ(16deg); }
    }

    /* PIE NARANJA Y NEGRO */
    .robot-pie {
        position: absolute;
        bottom: -18px;
        left: 50%;
        transform: translateX(-50%);
        width: 36px;
        height: 12px;
        background: linear-gradient(90deg, #000000 0%, #2a2a2a 30%, #ffaa00 50%, #ff9900 70%, #000000 100%);
        border: 3px solid #0d0d0d;
        border-radius: 50%;
        box-shadow: 
            0 6px 12px rgba(0,0,0,0.5),
            inset -2px -2px 6px rgba(0,0,0,0.3);
    }

    /* BURBUJA DE MENSAJE */
    .robot-mensaje {
        position: absolute;
        bottom: -90px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        border: 3px solid #ff5500;
        border-radius: 15px;
        padding: 14px 18px;
        font-size: 13px;
        font-weight: bold;
        color: #000000;
        white-space: wrap;
        max-width: 200px;
        text-align: center;
        box-shadow: 0 8px 16px rgba(0,0,0,0.3);
        opacity: 0;
        pointer-events: none;
        z-index: 200;
    }

    .robot-mensaje.mostrar {
        animation: mostrarMensaje 3.5s ease-in-out forwards;
    }

    .robot-mensaje::after {
        content: '';
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 10px solid transparent;
        border-right: 10px solid transparent;
        border-bottom: 12px solid white;
    }

    .robot-mensaje::before {
        content: '';
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 12px solid transparent;
        border-right: 12px solid transparent;
        border-bottom: 15px solid #ff5500;
    }

    @keyframes mostrarMensaje {
        0% {
            opacity: 0;
            transform: translateX(-50%) translateY(10px) scale(0.8);
        }
        10% {
            opacity: 1;
            transform: translateX(-50%) translateY(0px) scale(1);
        }
        90% {
            opacity: 1;
            transform: translateX(-50%) translateY(0px) scale(1);
        }
        100% {
            opacity: 0;
            transform: translateX(-50%) translateY(-30px) scale(0.8);
        }
    }

    /* INTERACCIÓN CON MOUSE */
    .robot-mascota:hover {
        animation: hoverRobot3d 0.4s ease-out forwards !important;
    }

    .robot-mascota:hover .robot-cabeza {
        box-shadow: 
            0 35px 70px rgba(0,0,0,0.4),
            inset -8px -8px 20px rgba(0,0,0,0.18),
            inset 8px 8px 20px rgba(255,255,255,0.7);
    }

    .robot-mascota:hover .robot-cuerpo {
        box-shadow: 
            0 30px 60px rgba(0,0,0,0.4),
            inset -8px -8px 20px rgba(0,0,0,0.15),
            inset 8px 8px 20px rgba(255,255,255,0.6);
    }

    .robot-mascota:hover .robot-pecho {
        box-shadow: 
            0 14px 28px rgba(0,0,0,0.4),
            inset -5px -5px 12px rgba(0,0,0,0.3),
            inset 5px 5px 12px rgba(255,255,255,0.4),
            0 0 40px rgba(255, 153, 0, 0.6);
    }

    @keyframes hoverRobot3d {
        0% { transform: scale(1) translateY(0); }
        50% { transform: scale(1.12) translateY(-20px); }
        100% { transform: scale(1.15) translateY(-30px); }
    }

    /* MAXARENA - Modal y estilos del mini juego */
    .max-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(2,6,23,0.6);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 24px;
    }
    .max-modal-overlay.active { display:flex; }
    .max-modal {
        width: min(980px, 96%);
        background: linear-gradient(180deg,#fff,#f7fbff);
        border-radius: 18px;
        box-shadow: 0 30px 80px rgba(2,43,91,0.45);
        display:flex;
        gap: 18px;
        overflow: hidden;
    }
    .max-left {
        width: 360px;
        padding: 24px;
        background: linear-gradient(135deg,#000000 0%, #ff3d00 100%);
        color: white;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        position:relative;
    }
    .max-left h2 { margin: 0 0 8px; font-size: 20px; }
    .max-left p { font-size: 14px; opacity: 0.95; }
    .max-right { flex:1; padding: 22px; }
    .max-instructions { background: #fff; border-radius: 12px; padding: 14px; box-shadow: 0 8px 20px rgba(2,43,91,0.06); }
    .max-board { display:flex; gap:12px; margin-top:16px; justify-content:center; }
    .max-card { width:110px; height:140px; border-radius:12px; background: linear-gradient(135deg,#fff,#f3f7ff); display:flex; align-items:center; justify-content:center; font-size:28px; cursor:pointer; box-shadow: 0 8px 18px rgba(2,43,91,0.08); border: 2px solid transparent; transition: transform .18s, box-shadow .18s; }
    .max-card:hover { transform: translateY(-6px); box-shadow: 0 18px 34px rgba(2,43,91,0.12); }
    .max-card.revealed { background: linear-gradient(135deg,#ffd, #fff); border-color:#ffb84d; }
    /* flip card */
    .max-card { perspective: 900px; }
    .card-inner { width:100%; height:100%; position:relative; transform-style:preserve-3d; transition: transform 0.7s; }
    .max-card.flipped .card-inner { transform: rotateY(180deg); }
    .card-front, .card-back { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; backface-visibility: hidden; border-radius:12px; }
    .card-front { background: linear-gradient(135deg,#fff,#f3f7ff); font-size:32px; color:#000000; }
    .card-back { transform: rotateY(180deg); background: linear-gradient(135deg,#fff9e6,#fff7f0); font-size:40px; color:#ff3d00; }
    .card-back .result-icon.x { color: #d64545; font-size:44px; }
    .card-back .result-icon.star { color: #f4c542; font-size:44px; }

    /* confetti */
    .confetti-piece { position: absolute; width: 10px; height: 16px; background: red; opacity: 0; transform: translateY(0) rotate(0); }
    @keyframes confettiFall { to { transform: translateY(400px) rotate(720deg); opacity: 1; } }

    /* Max face (win overlay) */
    #max-face.activo .robot-cabeza { animation: robotActivo 0.6s ease-out; }
    #max-face.activo .robot-ojos { animation: eyePulse 1300ms ease-in-out 0s 2; }

    .btn-game-action { margin-top: 14px; padding:10px 16px; border-radius:999px; border:none; cursor:pointer; background: linear-gradient(135deg,#ffb84d,#ff3d00); color:white; font-weight:700; }
    .max-footer-note { margin-top:12px; font-size:13px; color:#333; }
    @media(max-width:880px) { .max-modal{flex-direction:column;} .max-left{width:100%;} }
</style>
</head>

<body>

<!-- HEADER -->
<header class="header">
    <div class="header-top">
        <div class="logo-section">
            <h1 class="logo">
                <i class="fas fa-shopping-bag"></i> MAXIMA ONLINE STORE
            </h1>
        </div>
        
        <div class="header-right">
            <div class="user-section">
                <?php if(isset($_SESSION['usuario_id'])): ?>
                    <span style="color: #ff5500; margin-right: 15px;">👋 <?php echo substr($_SESSION['usuario_nombre'], 0, 15); ?></span>
                    <a href="perfil.php" class="user-icon" title="Mi Perfil">
                        <i class="fas fa-user"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="user-icon" title="Iniciar Sesión">
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                    <a href="registro.php" class="user-icon" title="Crear Cuenta" style="margin-left: 10px;">
                        <i class="fas fa-user-plus"></i>
                    </a>
                <?php endif; ?>
                
                <a href="carrito.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo $carrito_count; ?></span>
                </a>
                <a href="wishlist.php" class="wishlist-icon">
                    <i class="fas fa-heart"></i>
                    <span class="wishlist-count"><?php echo $wishlist_count; ?></span>
                </a>
            </div>
        </div>
    </div>

    <nav class="nav-menu">
        <a href="#" onclick="abrirJuegoModal(); return false;" class="btn-game" title="¡Juega y gana descuentos!"><i class="fas fa-gamepad"></i> MaxArena - JUEGO</a>
        
        <form method="GET" class="busqueda-form" style="display: flex; gap: 10px;">
            <input type="text" name="buscar" placeholder="Buscar productos..." class="input-busqueda" value="<?php echo htmlspecialchars($busqueda); ?>" required>
            <button type="submit" class="btn-buscar">
                <i class="fas fa-search"></i> Buscar
            </button>
            <?php if($busqueda): ?>
                <a href="index.php" class="btn-limpiar" title="Limpiar búsqueda">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </form>
        
        <?php if(isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] === 'admin@maximaonline.store'): ?>
        <a href="admin_panel.php" class="btn-admin">Administración</a>
        <?php endif; ?>
    </nav>
</header>

<!-- MAIN CONTAINER -->
<div class="main-container">

    <!-- SIDEBAR IZQUIERDO -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <h3>Categorías</h3>
            <ul>
                <li><a href="index.php" <?php echo !$categoria_filtro && !$ofertas ? 'class="active"' : ''; ?>>🏠 Todos los productos</a></li>
                <li><a href="index.php?ofertas=1" <?php echo $ofertas && !$categoria_filtro ? 'class="active"' : ''; ?>>🏷️ Todas las Ofertas</a></li>
                <li style="border-top: 1px solid #ddd; margin-top: 10px; padding-top: 10px;"></li>
                <li><a href="index.php?categoria=Productos smart" <?php echo ($categoria_filtro === 'Productos smart') ? 'class="active"' : ''; ?>>📱 Productos smart</a></li>
                <li><a href="index.php?categoria=Coventra smart" <?php echo ($categoria_filtro === 'Coventra smart') ? 'class="active"' : ''; ?>>🏠 Coventra smart</a></li>
                <li><a href="index.php?categoria=Novedades" <?php echo ($categoria_filtro === 'Novedades') ? 'class="active"' : ''; ?>>✨ Novedades</a></li>
                <li><a href="index.php?categoria=Tecnología" <?php echo ($categoria_filtro === 'Tecnología') ? 'class="active"' : ''; ?>>💻 Tecnología</a></li>
                <li><a href="index.php?categoria=Hogar" <?php echo ($categoria_filtro === 'Hogar') ? 'class="active"' : ''; ?>>🏡 Hogar</a></li>
                <li><a href="index.php?categoria=Moda" <?php echo ($categoria_filtro === 'Moda') ? 'class="active"' : ''; ?>>👔 Moda</a></li>
                <li><a href="index.php?categoria=Deportes" <?php echo ($categoria_filtro === 'Deportes') ? 'class="active"' : ''; ?>>⚽ Deportes</a></li>
                <li><a href="index.php?categoria=Pastamanías" <?php echo ($categoria_filtro === 'Pastamanías') ? 'class="active"' : ''; ?>>🍝 Pastamanías</a></li>
            </ul>
        </div>

        <form method="GET" class="sidebar-section filter-form" style="padding: 0;">
            <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
            <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($categoria_filtro); ?>">
            <?php if($ofertas): ?>
                <input type="hidden" name="ofertas" value="1">
            <?php endif; ?>

            <div class="sidebar-section">
                <h3>Precio</h3>
                <div class="price-filter">
                    <div style="display:flex; gap:10px; margin-bottom:10px;">
                        <label style="flex:1;">Desde
                            <input type="number" name="precio_min" min="0" step="1" value="<?php echo intval($precio_min); ?>" style="width: 100%;">
                        </label>
                        <label style="flex:1;">Hasta
                            <input type="number" name="precio_max" min="0" step="1" value="<?php echo intval($precio_max); ?>" style="width: 100%;">
                        </label>
                    </div>
                    <p>Filtra por rango de precio</p>
                </div>
            </div>

            <div class="sidebar-section">
                <h3>Marca</h3>
                <ul class="brand-list">
                    <?php
                    $marcas = ['Amazon', 'Apple', 'Samsung', 'LG', 'Sony'];
                    foreach($marcas as $marca):
                        $checked = in_array($marca, $marcas_seleccionadas) ? 'checked' : '';
                    ?>
                    <li>
                        <label style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="marca[]" value="<?php echo $marca; ?>" <?php echo $checked; ?>>
                            <?php echo $marca; ?>
                        </label>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-section" style="text-align:center; padding-bottom: 20px;">
                <button type="submit" class="btn-filtrar" style="width:100%; padding: 12px 0; border:none; background:#000000; color:#fff; border-radius:8px; cursor:pointer;">Aplicar filtros</button>
                <a href="index.php" class="btn-limpiar" style="display:inline-block; margin-top:10px; color:#000000;">Limpiar filtros</a>
            </div>
        </form>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="content">

        <!-- BANNER -->
        <div class="banner">
            <div class="banner-content">
                <h2>Descubre lo Mejor en MAXIMA ONLINE STORE</h2>
                <p>Los mejores productos al mejor precio</p>
            </div>
            <!-- ROBOT INTERACTIVO 3D -->
            <div id="robot-mascota" class="robot-mascota" onclick="robotCae()">
                <div class="robot-contenedor">
                    <!-- Burbuja de mensaje -->
                    <div id="robot-msg" class="robot-mensaje"></div>
                    
                    <!-- Antena con LED luminoso -->
                    <div class="robot-antena-top">
                        <div class="robot-antena-bola"></div>
                    </div>
                    
                    <!-- Cabeza -->
                    <div class="robot-cabeza">
                        <!-- Auriculares 3D -->
                        <div class="robot-auricular robot-auricular-izq"></div>
                        <div class="robot-auricular robot-auricular-der"></div>
                        
                        <!-- Pantalla LED digital -->
                        <div class="robot-pantalla">
                            <div class="robot-ojos">
                                <div class="robot-ojo robot-ojo-izq"></div>
                                <div class="robot-ojo robot-ojo-der"></div>
                            </div>
                            <div class="robot-boca"></div>
                        </div>
                    </div>
                    
                    <!-- Cuerpo principal -->
                    <div class="robot-cuerpo">
                        <!-- Panel del pecho con carrito -->
                        <div class="robot-pecho">
                            <span class="robot-carrito">🛒</span>
                        </div>
                    </div>

                    <!-- Brazos articulados -->
                    <div class="robot-brazo robot-brazo-izq">
                        <div class="robot-mano"></div>
                    </div>
                    <div class="robot-brazo robot-brazo-der">
                        <div class="robot-mano"></div>
                    </div>
                    
                    <!-- Piernas articuladas -->
                    <div class="robot-pierna robot-pierna-izq">
                        <div class="robot-pie"></div>
                    </div>
                    <div class="robot-pierna robot-pierna-der">
                        <div class="robot-pie"></div>
                    </div>
                </div>
            </div>

            <!-- MAXARENA: Modal del mini juego -->
            <div id="max-modal" class="max-modal-overlay" aria-hidden="true">
                <div class="max-modal" role="dialog" aria-modal="true">
                    <div class="max-left">
                        <h2><i class="fas fa-gamepad"></i> MaxArena</h2>
                        <p style="font-size:14px; color:#888; margin-top:5px;">🎮 MINI JUEGO - Gana Descuentos 🎁</p>
                        <p>Un mini-juego interactivo con Max — gana descuentos para tu compra.</p>
                        <div id="max-left-robot" style="margin-top:16px; width:180px; min-height:220px; display:flex; align-items:center; justify-content:center;">
                            <!-- El robot aparecerá aquí durante el juego -->
                        </div>
                        <button onclick="cerrarJuegoModal()" class="btn-game-action" style="margin-top:18px; background:#ffffff; color:#000000;">Cerrar</button>
                    </div>
                    <div class="max-right">
                        <div class="max-instructions">
                            <strong>Instrucciones</strong>
                            <p style="margin-top:8px; color:black;">Antes de empezar: necesitas al menos un artículo con precio mayor o igual a $100 para jugar. Si ganas obtendrás entre 10% y 25% de descuento aplicado al total. Si pierdes, deberás añadir un producto mayor a $100 para volver a intentarlo.</p>
                            <div class="max-footer-note">Nombre del juego: <strong>MaxArena - La Selección</strong></div>
                        </div>

                        <div id="max-status" style="margin-top:14px; display:flex; gap:10px; align-items:center;">
                            <div style="font-weight:700; color:#000000;">Estado:</div>
                            <div id="max-status-text">Listo para jugar</div>
                        </div>

                        <div class="max-board" id="max-board" aria-live="polite">
                            <div class="max-card" data-index="1" onclick="elegirCarta(1)">
                                <div class="card-inner">
                                    <div class="card-front">?</div>
                                    <div class="card-back"><span class="result-icon"></span></div>
                                </div>
                            </div>
                            <div class="max-card" data-index="2" onclick="elegirCarta(2)">
                                <div class="card-inner">
                                    <div class="card-front">?</div>
                                    <div class="card-back"><span class="result-icon"></span></div>
                                </div>
                            </div>
                            <div class="max-card" data-index="3" onclick="elegirCarta(3)">
                                <div class="card-inner">
                                    <div class="card-front">?</div>
                                    <div class="card-back"><span class="result-icon"></span></div>
                                </div>
                            </div>
                        </div>
                        <div id="max-win-overlay" style="display:none; position: absolute; inset:0; align-items:center; justify-content:center; pointer-events:none;">
                            <div id="max-face" class="robot-mascota" style="margin:0; left:0;">
                                <div class="robot-contenedor">
                                    <!-- Antena con LED luminoso -->
                                    <div class="robot-antena-top">
                                        <div class="robot-antena-bola"></div>
                                    </div>
                                    
                                    <!-- Cabeza -->
                                    <div class="robot-cabeza">
                                        <!-- Auriculares 3D -->
                                        <div class="robot-auricular robot-auricular-izq"></div>
                                        <div class="robot-auricular robot-auricular-der"></div>
                                        
                                        <!-- Pantalla LED digital -->
                                        <div class="robot-pantalla">
                                            <div class="robot-ojos">
                                                <div class="robot-ojo robot-ojo-izq"></div>
                                                <div class="robot-ojo robot-ojo-der"></div>
                                            </div>
                                            <div class="robot-boca"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Cuerpo principal -->
                                    <div class="robot-cuerpo">
                                        <!-- Panel del pecho con carrito -->
                                        <div class="robot-pecho">
                                            <span class="robot-carrito">🎁</span>
                                        </div>
                                    </div>

                                    <!-- Brazos articulados -->
                                    <div class="robot-brazo robot-brazo-izq">
                                        <div class="robot-mano"></div>
                                    </div>
                                    <div class="robot-brazo robot-brazo-der">
                                        <div class="robot-mano"></div>
                                    </div>
                                    
                                    <!-- Piernas articuladas -->
                                    <div class="robot-pierna robot-pierna-izq">
                                        <div class="robot-pie"></div>
                                    </div>
                                    <div class="robot-pierna robot-pierna-der">
                                        <div class="robot-pie"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; gap:10px; align-items:center; margin-top:12px;">
                            <button id="max-start-btn" class="btn-game-action" onclick="iniciarMaxArena()"><i class="fas fa-play-circle"></i> Jugar Ahora</button>
                            <button id="max-rules-btn" class="btn-game-action" style="background:#ffffff; color:#000000;" onclick="mostrarReglas()">Ver Reglas</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARRUSEL DE OFERTAS -->
        <section class="carrusel-ofertas">
            <div class="carrusel-container">
                <div class="carrusel-wrapper">
                    <?php foreach($carrusel_data as $index => $slide): ?>
                    <div class="carrusel-slide <?php echo $index === 0 ? 'carrusel-active' : ''; ?>" style="background: <?php echo htmlspecialchars($slide['gradiente']); ?>;">
                        <div class="carrusel-content">
                            <h3><?php echo htmlspecialchars($slide['titulo']); ?></h3>
                            <p><?php echo htmlspecialchars($slide['descripcion']); ?></p>
                            <span class="carrusel-badge"><?php echo htmlspecialchars($slide['etiqueta']); ?></span>
                        </div>
                        <img src="<?php echo htmlspecialchars($slide['imagen']); ?>" alt="<?php echo htmlspecialchars($slide['titulo']); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Controles del carrusel -->
                <button class="carrusel-btn carrusel-prev" onclick="moverCarrusel(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carrusel-btn carrusel-next" onclick="moverCarrusel(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <!-- Indicadores -->
                <div class="carrusel-indicators">
                    <?php for($i = 0; $i < count($carrusel_data); $i++): ?>
                    <span class="indicator" onclick="irAlCarrusel(<?php echo $i; ?>)"></span>
                    <?php endfor; ?>
                </div>
            </div>
        </section>

        <!-- OFERTAS DEL DÍA -->
        <section class="ofertas-section">
            <div class="ofertas-header">
                <h2><?php echo $titulo_categoria; ?></h2>
            </div>

            <div class="productos-grid">
                <?php 
                if($resultado->num_rows > 0) {
                    while($fila = $resultado->fetch_assoc()) { 
                ?>

                <div class="producto-card">
                    <div class="producto-img-container">
                        <?php if(!empty($fila['imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($fila['imagen']); ?>" alt="<?php echo htmlspecialchars($fila['nombre_producto']); ?>" class="producto-img">
                        <?php else: ?>
                            <div class="placeholder-img">
                                <i class="fas fa-image"></i>
                                <p>Sin imagen</p>
                            </div>
                        <?php endif; ?>
                        <?php if($fila['descuento'] > 0): ?>
                        <span class="descuento-badge">-<?php echo $fila['descuento']; ?>%</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="producto-info">
                        <h3 class="producto-nombre"><?php echo substr($fila['nombre_producto'], 0, 50); ?></h3>
                        
                        <div class="rating">
                            <?php 
                            $calificacion = $fila['calificacion'];
                            for($i = 1; $i <= 5; $i++) {
                                if($i <= $calificacion) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif($i - 0.5 <= $calificacion) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="fas fa-star" style="color: #ddd;"></i>';
                                }
                            }
                            ?>
                            <span>(<?php echo intval($fila['calificacion'] * 10); ?> votos)</span>
                        </div>

                        <div class="precio-section">
                            <?php 
                            if($fila['descuento'] > 0) {
                                $precio_original = $fila['precio'] * (1 + ($fila['descuento'] / 100));
                                echo '<p class="precio-original"><strike>$' . number_format($precio_original, 2) . '</strike></p>';
                            }
                            ?>
                            <p class="precio-actual">$<?php echo number_format($fila['precio'], 2); ?></p>
                        </div>

                        <p class="stock-info">Stock: <strong><?php echo $fila['stock']; ?></strong></p>
                        
                        <p class="descripcion-corta"><?php echo substr($fila['descripcion'], 0, 80) . "..."; ?></p>

                        <div class="producto-acciones">
                            <button class="btn-carrito" onclick="abrirModal(<?php echo $fila['idproductos']; ?>, '<?php echo addslashes(htmlspecialchars($fila['nombre_producto'])); ?>', <?php echo $fila['precio']; ?>, '<?php echo htmlspecialchars($fila['imagen']); ?>', '<?php echo addslashes(htmlspecialchars($fila['descripcion'])); ?>', <?php echo $fila['stock']; ?>, <?php echo $fila['descuento']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Ver detalles
                            </button>
                            
                            <div class="admin-btns">
                                <a href="agregar_wishlist.php?id=<?php echo $fila['idproductos']; ?>&return=index.php?categoria=<?php echo urlencode($categoria_filtro); ?>">
                                    <button class="btn-wishlist" title="Agregar a Favoritos">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </a>
                                <?php 
                                // Solo mostrar botones de editar y eliminar si es el administrador autorizado
                                if(isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] === 'admin@maximaonline.store') {
                                ?>
                                <a href="editar.php?id=<?php echo $fila['idproductos']; ?>">
                                    <button class="btn-editar" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </a>
                                <a href="eliminar.php?id=<?php echo $fila['idproductos']; ?>">
                                    <button class="btn-eliminar" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </a>
                                <?php 
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php 
                    }
                } else {
                    echo '<p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">No hay productos en esta categoría.</p>';
                }
                ?>
            </div>
        </section>

    </main>

</div>

<!-- MODAL DE DETALLE DE PRODUCTO -->
<div id="modal-producto" class="modal-overlay">
    <div class="modal-contenido">
        <button class="modal-cerrar" onclick="cerrarModal()">✕</button>
        
        <div class="modal-cuerpo">
            <div class="modal-imagen-section">
                <img id="modal-imagen" src="" alt="Producto" class="modal-imagen">
                <span id="modal-descuento" class="descuento-badge" style="position: absolute; top: 10px; right: 10px;"></span>
            </div>
            
            <div class="modal-detalles">
                <h2 id="modal-nombre"></h2>
                <p id="modal-precio" class="modal-precio"></p>
                <p id="modal-stock" class="modal-stock"></p>
                
                <div class="modal-descripcion-section">
                    <h4>Descripción:</h4>
                    <p id="modal-descripcion" class="modal-descripcion"></p>
                </div>
                
                <!-- SECCIÓN DE RESEÑAS -->
                <div class="modal-resenas-section">
                    <h4>Reseñas de clientes:</h4>
                    <div id="resenas-contenedor" class="resenas-contenedor"></div>
                </div>
                
                <!-- FORMULARIO DE RESEÑA -->
                <form id="form-resena" class="form-resena" onsubmit="enviarResena(event)">
                    <input type="hidden" id="modal-idproducto">
                    
                    <div class="form-grupo">
                        <label for="resena-calificacion">Tu calificación:</label>
                        <select id="resena-calificacion" required>
                            <option value="5">★★★★★ Excelente</option>
                            <option value="4">★★★★☆ Muy bueno</option>
                            <option value="3">★★★☆☆ Bueno</option>
                            <option value="2">★★☆☆☆ Regular</option>
                            <option value="1">★☆☆☆☆ Malo</option>
                        </select>
                    </div>
                    
                    <div class="form-grupo">
                        <label for="resena-comentario">Tu comentario (opcional):</label>
                        <textarea id="resena-comentario" placeholder="Comparte tu experiencia con este producto..." maxlength="500" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn-modal-resena">Enviar reseña</button>
                </form>
                
                <!-- SELECTOR DE CANTIDAD Y BOTÓN AGREGAR AL CARRITO -->
                <form onsubmit="agregarAlCarritoModal(event)" style="margin-top: 20px;">
                    <div class="cantidad-section" style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <label for="modal-cantidad" style="font-weight: 600; color: #000000;">Cantidad:</label>
                        <input type="number" id="modal-cantidad" name="cantidad" min="1" max="99" value="1" style="width: 60px; padding: 8px; border: 1px solid #ddd; border-radius: 5px; text-align: center; font-size: 14px;">
                    </div>
                    <button type="submit" class="btn-modal-carrito">
                        <i class="fas fa-shopping-cart"></i> Agregar al carrito
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4>Información</h4>
            <ul>
                <li><a href="#">Acerca de nosotros</a></li>
                <li><a href="#">Estimación de envío</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Servicio al cliente</h4>
            <ul>
                <li><a href="#">Contacto</a></li>
                <li><a href="#">Devoluciones</a></li>
                <li><a href="#">Política de privacidad</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Pago y manalas</h4>
            <div class="payment-methods">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-paypal"></i>
                <i class="fab fa-cc-amex"></i>
            </div>                   
        </div>

        <div class="footer-section" style="text-align: center;">
            <h4>Escanea nuestro Código QR</h4>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=http://localhost/tiendaonline" alt="Código QR - Accede a nuestra tienda" style="width: 150px; height: 150px; margin-top: 10px;">
            <p style="font-size: 12px; margin-top: 8px; color: #666;">Escanea para acceder a nuestra tienda</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2024 MAXIMA ONLINE STORE. Todos los derechos reservados.</p>
    </div>
</footer>

<script>
let carruselIndex = 0;
let carruselInterval;

function mostrarCarrusel(n) {
    const slides = document.querySelectorAll('.carrusel-slide');
    const indicators = document.querySelectorAll('.indicator');
    
    if (n >= slides.length) {
        carruselIndex = 0;
    }
    if (n < 0) {
        carruselIndex = slides.length - 1;
    }
    
    slides.forEach(slide => slide.classList.remove('carrusel-active'));
    indicators.forEach(ind => ind.classList.remove('active'));
    
    slides[carruselIndex].classList.add('carrusel-active');
    indicators[carruselIndex].classList.add('active');
}

function moverCarrusel(n) {
    clearInterval(carruselInterval);
    carruselIndex += n;
    mostrarCarrusel(carruselIndex);
    iniciarAutoplay();
}

function irAlCarrusel(n) {
    clearInterval(carruselInterval);
    carruselIndex = n;
    mostrarCarrusel(carruselIndex);
    iniciarAutoplay();
}

function iniciarAutoplay() {
    carruselInterval = setInterval(() => {
        carruselIndex++;
        mostrarCarrusel(carruselIndex);
    }, 8000); // Cambiar imagen cada 8 segundos
}

// Inicializar carrusel
document.addEventListener('DOMContentLoaded', () => {
    mostrarCarrusel(carruselIndex);
    iniciarAutoplay();
});

// FUNCIÓN DEL ROBOT
function robotCae() {
    const robot = document.getElementById('robot-mascota');
    const mensaje = document.getElementById('robot-msg');
    
    // Remover clases anteriores
    robot.classList.remove('activo');
    mensaje.classList.remove('mostrar');
    
    // Agregar animación de pulsación
    robot.classList.add('activo');
    
    // Mostrar mensaje de bienvenida
    const textoMensaje = "Hola humano soy Max listo para ayudarte";
    mensaje.textContent = textoMensaje + " 🤖";
    mensaje.classList.add('mostrar');
    
    // Función para hablar el mensaje usando Web Speech API
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();

        const vozPreferida = (voices) => {
            const prioridades = [
                /es-(mx|us|ar|co|cl|pe|uy|ve|do)/i,
                /google.*español/i,
                /español.*google/i,
                /latino/i,
                /mex/i,
                /spanish/i
            ];

            for (const regex of prioridades) {
                const voz = voices.find(v => regex.test(v.name) || regex.test(v.lang));
                if (voz) return voz;
            }
            return voices.find(v => /es-/i.test(v.lang)) || null;
        };

        const hablar = () => {
            const utterance = new SpeechSynthesisUtterance(textoMensaje);
            utterance.lang = 'es-MX';
            utterance.rate = 0.95;
            utterance.pitch = 1.15;
            utterance.volume = 1;

            const voces = speechSynthesis.getVoices();
            const voz = vozPreferida(voces);
            if (voz) {
                utterance.voice = voz;
            }

            speechSynthesis.speak(utterance);
        };

        if (speechSynthesis.getVoices().length > 0) {
            hablar();
        } else {
            speechSynthesis.onvoiceschanged = () => {
                hablar();
                speechSynthesis.onvoiceschanged = null;
            };
        }
    }
    
    // Después de 3500ms, quitar las clases para que vuelva a la normalidad
    setTimeout(() => {
        robot.classList.remove('activo');
        mensaje.classList.remove('mostrar');
        mensaje.textContent = '';
    }, 3500);
}

/* MODAL DE DETALLE DE PRODUCTO */
function abrirModal(idproducto, nombre, precio, imagen, descripcion, stock, descuento) {
    const modal = document.getElementById('modal-producto');
    document.getElementById('modal-nombre').textContent = nombre;
    document.getElementById('modal-precio').textContent = '$' + parseFloat(precio).toFixed(2);
    document.getElementById('modal-imagen').src = imagen;
    document.getElementById('modal-descripcion').textContent = descripcion;
    document.getElementById('modal-stock').textContent = 'Stock disponible: ' + stock;
    document.getElementById('modal-idproducto').value = idproducto;
    document.getElementById('modal-cantidad').max = stock;
    
    if (descuento > 0) {
        document.getElementById('modal-descuento').style.display = 'inline';
        document.getElementById('modal-descuento').textContent = '-' + descuento + '%';
    } else {
        document.getElementById('modal-descuento').style.display = 'none';
    }
    
    document.getElementById('modal-cantidad').value = 1;
    modal.style.display = 'flex';
    cargarResenas(idproducto);
}

function cerrarModal() {
    document.getElementById('modal-producto').style.display = 'none';
    document.getElementById('form-resena').reset();
}

function cargarResenas(idproducto) {
    const contenedor = document.getElementById('resenas-contenedor');
    if (!contenedor) return;
    contenedor.innerHTML = '<p style="text-align:center; color:#999;">Cargando reseñas...</p>';
    
    fetch('obtener_resenas.php?idproductos=' + idproducto)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.total > 0) {
                let html = '<div class="resenas-list">';
                data.resenas.forEach(resena => {
                    html += '<div class="resena-item">' +
                        '<div class="resena-header">' +
                        '<strong>' + escapeHtml(resena.usuario) + '</strong> ' +
                        '<span class="resena-estrellas" style="color:#ff5500;">' + resena.estrellas + '</span>' +
                        '</div>' +
                        '<p class="resena-fecha">' + resena.fecha + '</p>' +
                        '<p class="resena-comentario">' + escapeHtml(resena.comentario || 'Sin comentario') + '</p>' +
                        '</div>';
                });
                html += '</div>';
                contenedor.innerHTML = html;
            } else {
                contenedor.innerHTML = '<p style="color:#999; text-align:center;">No hay reseñas aún. ¡Sé el primero!</p>';
            }
        })
        .catch(error => {
            contenedor.innerHTML = '<p style="color:#e74c3c;">Error al cargar reseñas</p>';
        });
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function enviarResena(event) {
    event.preventDefault();
    
    const idproducto = document.getElementById('modal-idproducto').value;
    const calificacion = document.getElementById('resena-calificacion').value;
    const comentario = document.getElementById('resena-comentario').value;
    const boton = document.querySelector('#form-resena button');
    
    boton.disabled = true;
    boton.textContent = 'Guardando...';
    
    const formData = new FormData();
    formData.append('idproductos', idproducto);
    formData.append('calificacion', calificacion);
    formData.append('comentario', comentario);
    
    fetch('guardar_resena.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('form-resena').reset();
            boton.textContent = '✓ Reseña enviada';
            setTimeout(() => {
                boton.textContent = 'Enviar reseña';
                boton.disabled = false;
                cargarResenas(idproducto);
            }, 1500);
        } else {
            alert(data.message);
            boton.disabled = false;
            boton.textContent = 'Enviar reseña';
        }
    })
    .catch(error => {
        alert('Error al enviar reseña');
        boton.disabled = false;
        boton.textContent = 'Enviar reseña';
    });
}

function agregarAlCarritoModal(event) {
    event.preventDefault();
    const idproducto = document.getElementById('modal-idproducto').value;
    const cantidad = document.getElementById('modal-cantidad').value;
    window.location.href = 'agregar_carrito.php?id=' + idproducto + '&cantidad=' + cantidad + '&from=modal';
}

// Cerrar modal al hacer clic fuera
window.addEventListener('click', (event) => {
    const modal = document.getElementById('modal-producto');
    if (event.target === modal) {
        cerrarModal();
    }
});

// MaxArena - lógica del mini juego
let canPlayMaxArena = false; // estado global de si puede jugar
const MAX_INTENTOS = 3;

function obtenerIntentosRestantes() {
    return parseInt(sessionStorage.getItem('maxarena_intentos') || MAX_INTENTOS);
}

function decrementarIntentos() {
    const intentos = obtenerIntentosRestantes();
    const nuevosIntentos = Math.max(0, intentos - 1);
    sessionStorage.setItem('maxarena_intentos', nuevosIntentos);
    return nuevosIntentos;
}

function resetearIntentos() {
    sessionStorage.setItem('maxarena_intentos', MAX_INTENTOS);
}

function abrirJuegoModal() {
    const overlay = document.getElementById('max-modal');
    if (!overlay) {
        alert('Error: No se pudo encontrar el modal del juego');
        return;
    }
    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden', 'false');
    
    // Mostrar el robot en el lado izquierdo
    mostrarRobotEnJuego();
    
    const intentos = obtenerIntentosRestantes();
    
    // pedir estado al servidor
    fetch('juego_descuento.php?action=estado')
        .then(r => {
            if (!r.ok) throw new Error('Error del servidor: ' + r.status);
            return r.json();
        })
        .then(data => {
            const status = document.getElementById('max-status-text');
            const startBtn = document.getElementById('max-start-btn');
            
            if (!status || !startBtn) {
                alert('Error: Elementos del juego no encontrados');
                return;
            }
            
            canPlayMaxArena = data.canPlay; // guardar estado global
            
            // CASO 0: Ya ganó antes (juego completado)
            if (data.juego_ganado) {
                status.innerHTML = '🏆 ¡YA GANASTE! Tu juego está completo.<br><small>No puedes jugar más.</small>';
                startBtn.disabled = true;
                startBtn.style.opacity = '0.2';
                startBtn.style.cursor = 'not-allowed';
            }
            // CASO 1: Intentos agotados (cliente)
            else if (intentos <= 0) {
                status.innerHTML = '❌ Se acabaron tus 3 intentos.<br><small>Vuelve más tarde.</small>';
                startBtn.disabled = true;
                startBtn.style.opacity = '0.2';
                startBtn.style.cursor = 'not-allowed';
                canPlayMaxArena = false;
            }
            // CASO 2: Compra requerida (servidor)
            else if (data.compra_requerida) {
                status.textContent = '⚠️ Ya gastaste tu intento. Debes hacer una compra MÍNIMA de $100 para poder jugar de nuevo. Ve a tu carrito y completa la compra.';
                startBtn.disabled = true;
                startBtn.style.opacity = '0.3';
                startBtn.style.cursor = 'not-allowed';
            }
            // CASO 3: Mensaje bloqueado (carrito vacío, etc)
            else if (data.mensaje_bloqueado) {
                status.textContent = '❌ ' + data.mensaje_bloqueado;
                startBtn.disabled = true;
                startBtn.style.opacity = '0.5';
                startBtn.style.cursor = 'not-allowed';
            }
            // CASO 4: Ya tiene descuento activo
            else if (data.juego_descuento && data.juego_descuento > 0) {
                status.innerHTML = '✅ Tienes un descuento activo de ' + data.juego_descuento + '%.<br><small>Úsalo al pagar. Intentos restantes: ' + intentos + '/3</small>';
                startBtn.disabled = true;
                startBtn.style.opacity = '0.5';
            }
            // CASO 5: Listo para jugar
            else {
                status.innerHTML = '🎮 Listo para jugar. Premio: 10% - 25%<br><small>Intentos restantes: ' + intentos + '/3</small>';
                startBtn.disabled = false;
                startBtn.style.opacity = '1';
                startBtn.style.cursor = 'pointer';
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            canPlayMaxArena = false;
            const status = document.getElementById('max-status-text');
            if (status) {
                status.textContent = '⚠️ Error al validar. Asegúrate de tener un producto >= $100 en el carrito.';
            }
        });
}

function mostrarRobotEnJuego() {
    const container = document.getElementById('max-left-robot');
    if (!container) return;
    
    container.innerHTML = `
        <div class="robot-mascota" style="margin:0; left:0; animation: flotarRobot3d 4s ease-in-out infinite; transform: scale(0.65);">
            <div class="robot-contenedor">
                <div class="robot-antena-top">
                    <div class="robot-antena-bola"></div>
                </div>
                <div class="robot-cabeza">
                    <div class="robot-auricular robot-auricular-izq"></div>
                    <div class="robot-auricular robot-auricular-der"></div>
                    <div class="robot-pantalla">
                        <div class="robot-ojos">
                            <div class="robot-ojo robot-ojo-izq"></div>
                            <div class="robot-ojo robot-ojo-der"></div>
                        </div>
                        <div class="robot-boca"></div>
                    </div>
                </div>
                <div class="robot-cuerpo">
                    <div class="robot-pecho">
                        <span class="robot-carrito">🎮</span>
                    </div>
                </div>
                <div class="robot-brazo robot-brazo-izq">
                    <div class="robot-mano"></div>
                </div>
                <div class="robot-brazo robot-brazo-der">
                    <div class="robot-mano"></div>
                </div>
                <div class="robot-pierna robot-pierna-izq">
                    <div class="robot-pie"></div>
                </div>
                <div class="robot-pierna robot-pierna-der">
                    <div class="robot-pie"></div>
                </div>
            </div>
        </div>
    `;
}

function cerrarJuegoModal() {
    const overlay = document.getElementById('max-modal');
    const winOverlay = document.getElementById('max-win-overlay');
    const face = document.getElementById('max-face');
    const leftRobot = document.getElementById('max-left-robot');
    
    if (overlay) {
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
    }
    
    // Ocultar y limpiar el robot ganador
    if (winOverlay) {
        winOverlay.style.display = 'none';
        // Limpiar el confetti
        winOverlay.querySelectorAll('.confetti-piece').forEach(p => p.remove());
    }
    
    // Limpiar la animación del robot ganador
    if (face) {
        face.classList.remove('activo');
    }
    
    // Limpiar el robot del lado izquierdo
    if (leftRobot) {
        leftRobot.innerHTML = '';
    }
}

function mostrarReglas() {
    alert('Reglas:\n- Selecciona una de 3 cartas.\n- Si aciertas ganas entre 10% y 25% de descuento.\n- Si pierdes, necesitas un producto >= $100 para volver a jugar.');
}

function iniciarMaxArena() {
    // Validar intentos
    if (obtenerIntentosRestantes() <= 0) {
        alert('❌ Se acabaron tus 3 intentos.\n\nHaz una compra de $100+ para poder jugar de nuevo.');
        return;
    }
    
    if (!canPlayMaxArena) {
        alert('❌ No puedes jugar.\n\nRegla: Necesitas un producto con precio >= $100 en tu carrito.\n\nAñade un producto de ese valor y vuelve a intentar.');
        return;
    }
    
    const status = document.getElementById('max-status-text');
    if (status) {
        status.textContent = '🎯 Elige una carta...';
    }
    
    // reset cards (preserve inner structure)
    const cards = document.querySelectorAll('.max-card');
    if (cards.length === 0) {
        alert('Error: No se encontraron las cartas del juego');
        return;
    }
    
    cards.forEach(c => {
        c.classList.remove('revealed','flipped');
        c.style.pointerEvents = 'auto';
        const front = c.querySelector('.card-front');
        const back = c.querySelector('.card-back .result-icon');
        if (front) front.textContent = '?';
        if (back) back.textContent = '';
    });
}

function elegirCarta(n) {
    // Validar que todavía pueda jugar
    if (!canPlayMaxArena) {
        alert('❌ No puedes jugar.\n\nRegla: Necesitas un producto con precio >= $100 en tu carrito.');
        return;
    }
    
    // Validar intentos
    if (obtenerIntentosRestantes() <= 0) {
        alert('❌ Se acabaron tus 3 intentos. Vuelve más tarde.');
        return;
    }
    
    // bloquear selección rápida
    const cards = document.querySelectorAll('.max-card');
    if (cards.length === 0) {
        alert('Error: No se encontraron las cartas del juego');
        return;
    }
    
    cards.forEach(c => c.style.pointerEvents = 'none');
    const ganador = Math.floor(Math.random() * 3) + 1;
    
    // flip all cards with delay
    cards.forEach((c, idx) => {
        setTimeout(() => {
            c.classList.add('flipped');
        }, 300 + idx * 220);
    });

    // after flip reveal results
    setTimeout(() => {
        cards.forEach(c => c.classList.add('revealed'));
        cards.forEach(c => {
            const backIcon = c.querySelector('.result-icon');
            const ix = parseInt(c.getAttribute('data-index'));
            if (ix === ganador) {
                if (backIcon) {
                    backIcon.className = 'result-icon star';
                    backIcon.textContent = '★';
                }
            } else {
                if (backIcon) {
                    backIcon.className = 'result-icon x';
                    backIcon.textContent = '✕';
                }
            }
        });

        if (n === ganador) {
            // win: show Max face and confetti
            mostrarMaxWin();
            enviarResultado('win');
        } else {
            enviarResultado('loss');
        }
    }, 1200);
}

function mostrarMaxWin() {
    const overlay = document.getElementById('max-win-overlay');
    const face = document.getElementById('max-face');
    
    if (!overlay) {
        console.error('Error: No se encontró max-win-overlay');
        return;
    }
    
    overlay.style.display = 'flex';
    if (face) {
        face.classList.add('activo');
    } else {
        console.error('Error: No se encontró max-face');
    }
    
    // confetti
    for (let i=0;i<24;i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.left = (20 + Math.random()*80) + '%';
        piece.style.background = ['#ff3d00','#ffd700','#ff5c7a','#4ad1ff'][Math.floor(Math.random()*4)];
        piece.style.animation = `confettiFall ${0.9 + Math.random()*1.6}s linear forwards`;
        piece.style.transform = `translateY(-40px) rotate(${Math.random()*360}deg)`;
        overlay.appendChild(piece);
        setTimeout(()=> piece.remove(), 3000 + Math.random()*1200);
    }
    // El robot se queda visible hasta que cierres el modal
}
function enviarResultado(resultado) {
    fetch('juego_descuento.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'resultadoJuego', resultado: resultado })
    })
    .then(r => r.json())
    .then(data => {
        const status = document.getElementById('max-status-text');
        const startBtn = document.getElementById('max-start-btn');
        
        if (!data.success) {
            status.textContent = data.message || 'Error en el juego';
            canPlayMaxArena = false;
            return;
        }
        
        if (data.ganado) {
            // GANÓ - Bloquear permanentemente
            status.innerHTML = '🎉 ¡GANASTE ' + data.descuento + '% de descuento!<br><small>🏆 ¡Felicidades! Tu juego fue exitoso.</small>';
            canPlayMaxArena = false;
            
            // Guardar que ganó para bloquear en futuras sesiones
            sessionStorage.setItem('maxarena_juego_ganado', 'true');
            
            if (startBtn) {
                startBtn.disabled = true;
                startBtn.style.opacity = '0.2';
            }
        } else {
            // PERDIÓ - Decrementar intentos
            const intentosRestantes = decrementarIntentos();
            
            if (intentosRestantes > 0) {
                status.innerHTML = '😞 Perdiste. Debes pagar $100+ para reintentar.<br><small>Intentos restantes: ' + intentosRestantes + '/3</small>';
            } else {
                // Se acabaron los intentos
                status.innerHTML = '❌ Se acabaron tus 3 intentos.<br><small>Vuelve más tarde o haz una compra de $100+.</small>';
            }
            
            canPlayMaxArena = false;
            if (startBtn) {
                startBtn.disabled = true;
                startBtn.style.opacity = '0.3';
            }
        }
    })
    .catch(()=>{
        document.getElementById('max-status-text').textContent = '⚠️ Error de conexión con el servidor';
        canPlayMaxArena = false;
    });
}

</script>

</body>
</html>
