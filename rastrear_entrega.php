<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$idusuarios = $_SESSION['usuario_id'];
$identrega = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(!$identrega) {
    header("Location: index.php");
    exit();
}

// Obtener datos de la entrega
$sql = "SELECT e.*, r.nombre, r.telefono, r.latitud, r.longitud, 
               d.calle, d.numero, d.apartamento, d.ciudad, d.estado, d.codigo_postal,
               p.total, p.fecha_pedido
        FROM entregas e
        LEFT JOIN repartidores r ON e.idrepartidor = r.idrepartidor
        LEFT JOIN direcciones_entrega d ON e.iddireccion = d.iddireccion
        LEFT JOIN pedidos p ON e.idpedidos = p.idpedidos
        WHERE e.identrega = $identrega AND e.idusuarios = $idusuarios";

$resultado = $conexion->query($sql);

if($resultado->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$entrega = $resultado->fetch_assoc();

// Obtener historial
$sql_historial = "SELECT * FROM historial_entrega WHERE identrega = $identrega ORDER BY fecha_evento DESC";
$resultado_historial = $conexion->query($sql_historial);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rastrear Mi Entrega - MAXIMA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .rastreo-container {
        max-width: 1200px;
        margin: 30px auto;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        padding: 0 20px;
    }

    .mapa-rastreo-panel {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    #map-rastreo {
        width: 100%;
        height: 500px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .info-rastreo-panel {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .estado-entrega {
        background: linear-gradient(135deg, #000000 0%, #000000 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
    }

    .estado-badge {
        display: inline-block;
        padding: 8px 16px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .estado-titulo {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .tiempo-estimado {
        font-size: 18px;
        font-weight: 600;
    }

    .repartidor-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #ff5500;
    }

    .repartidor-info h3 {
        margin: 0 0 10px 0;
        color: #000000;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .repartidor-detalle {
        font-size: 14px;
        color: #666;
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .btn-contactar {
        width: 100%;
        padding: 10px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .btn-contactar:hover {
        background: #218838;
    }

    .direccion-entrega {
        background: #e8f4f8;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #00D9FF;
    }

    .direccion-entrega h3 {
        margin: 0 0 10px 0;
        color: #000000;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .historial-evento {
        padding: 12px;
        border-left: 3px solid #ddd;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .historial-evento.completado {
        border-left-color: #28a745;
        background: #f0f8f0;
    }

    .historial-evento.en-progreso {
        border-left-color: #ff5500;
        background: #fff8f0;
    }

    .evento-hora {
        font-size: 12px;
        color: #999;
        margin-bottom: 3px;
    }

    .evento-mensaje {
        font-weight: 600;
        color: #000000;
        margin-bottom: 3px;
    }

    .evento-detalles {
        font-size: 12px;
        color: #666;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #ddd;
    }

    @media (max-width: 900px) {
        .rastreo-container {
            grid-template-columns: 1fr;
        }

        #map-rastreo {
            height: 300px;
        }
    }

    .estado-pendiente { color: #ffc107; }
    .estado-recogida { color: #17a2b8; }
    .estado-en-camino { color: #ff5500; }
    .estado-entregando { color: #28a745; }
    .estado-entregado { color: #28a745; }
    .estado-cancelada { color: #dc3545; }
</style>
</head>
<body>

<header class="header">
    <div class="header-content">
        <div class="logo">
            <i class="fas fa-cube"></i> MAXIMA ONLINE STORE
        </div>
        <nav class="nav">
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="carrito.php">🛒 Carrito</a></li>
                <li><a href="ver_ordenes.php">📦 Mis Órdenes</a></li>
                <li><a href="logout.php">🚪 Salir</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="rastreo-container">
    <!-- Mapa con rastreo -->
    <div class="mapa-rastreo-panel">
        <h2><i class="fas fa-map"></i> Tu Entrega en Tiempo Real</h2>
        <div id="map-rastreo"></div>
    </div>

    <!-- Panel de información -->
    <div class="info-rastreo-panel">
        <!-- Estado de entrega -->
        <div class="estado-entrega">
            <div class="estado-badge">
                <i class="fas fa-info-circle"></i>
                <?php echo strtoupper(str_replace('_', ' ', $entrega['estado'])); ?>
            </div>
            <div class="estado-titulo" id="estado-titulo">
                <?php 
                $textos = [
                    'pendiente' => '⏳ En Preparación',
                    'recogida' => '📦 Recogido',
                    'en_camino' => '🚗 En Camino',
                    'entregando' => '📍 Entregando',
                    'entregado' => '✅ Entregado',
                    'cancelada' => '❌ Cancelada'
                ];
                echo $textos[$entrega['estado']] ?? 'Estado desconocido';
                ?>
            </div>
            <div class="tiempo-estimado" id="tiempo-estimado">
                <?php if($entrega['tiempo_estimado_minutos']): ?>
                ⏱️ <?php echo $entrega['tiempo_estimado_minutos']; ?> minutos aprox.
                <?php endif; ?>
            </div>
        </div>

        <!-- Información del repartidor -->
        <?php if($entrega['nombre']): ?>
        <div class="repartidor-info">
            <h3><i class="fas fa-user-check"></i> Tu Repartidor</h3>
            <div class="repartidor-detalle">
                <span><strong><?php echo htmlspecialchars($entrega['nombre']); ?></strong></span>
            </div>
            <div class="repartidor-detalle">
                <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($entrega['telefono']); ?></span>
            </div>
            <button class="btn-contactar">
                <i class="fas fa-phone"></i> Llamar Repartidor
            </button>
            <button class="btn-contactar" style="background: #17a2b8;">
                <i class="fas fa-comment"></i> Enviar Mensaje
            </button>
        </div>
        <?php endif; ?>

        <!-- Dirección de entrega -->
        <div class="direccion-entrega">
            <h3><i class="fas fa-map-marker-alt"></i> Dirección de Entrega</h3>
            <div style="font-size: 14px; line-height: 1.6;">
                <strong><?php echo htmlspecialchars($entrega['calle'] . ' ' . $entrega['numero']); ?></strong><br>
                <?php if($entrega['apartamento']): ?>
                    Apartamento <?php echo htmlspecialchars($entrega['apartamento']); ?><br>
                <?php endif; ?>
                <?php echo htmlspecialchars($entrega['ciudad'] . ', ' . $entrega['estado'] . ' ' . $entrega['codigo_postal']); ?>
            </div>
        </div>

        <!-- Historial de eventos -->
        <h3><i class="fas fa-history"></i> Historial</h3>
        <div class="timeline">
            <?php while($evento = $resultado_historial->fetch_assoc()): ?>
            <div class="historial-evento <?php echo in_array($evento['estado'], ['entregado', 'cancelada']) ? 'completado' : 'en-progreso'; ?>">
                <div class="evento-hora"><?php echo date('H:i', strtotime($evento['fecha_evento'])); ?></div>
                <div class="evento-mensaje"><?php echo htmlspecialchars($evento['estado']); ?></div>
                <div class="evento-detalles"><?php echo htmlspecialchars($evento['mensaje']); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA0-7LVgN_J_XvVx_dIjbL_Z-4YQnXZsAQ&language=es"></script>

<script>
    let mapaRastreo;
    let marcadorRepartidor;
    let marcadorEntrega;
    let ruta;

    const datosEntrega = {
        repartidor: {
            lat: <?php echo $entrega['latitud'] ?? -3.745404; ?>,
            lng: <?php echo $entrega['longitud'] ?? -38.523659; ?>
        },
        entrega: {
            lat: <?php echo $entrega['latitud'] ?? -3.745404; ?>,
            lng: <?php echo $entrega['longitud'] ?? -38.523659; ?>
        }
    };

    function inicializarMapaRastreo() {
        const centroMapa = {
            lat: (datosEntrega.repartidor.lat + datosEntrega.entrega.lat) / 2,
            lng: (datosEntrega.repartidor.lng + datosEntrega.entrega.lng) / 2
        };

        mapaRastreo = new google.maps.Map(document.getElementById('map-rastreo'), {
            zoom: 14,
            center: centroMapa,
            mapTypeControl: true,
            streetViewControl: true
        });

        // Marcador del repartidor
        marcadorRepartidor = new google.maps.Marker({
            position: datosEntrega.repartidor,
            map: mapaRastreo,
            title: 'Repartidor',
            icon: 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png'
        });

        // Marcador de destino
        marcadorEntrega = new google.maps.Marker({
            position: datosEntrega.entrega,
            map: mapaRastreo,
            title: 'Tu Dirección',
            icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
        });

        // Dibujar ruta
        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({ map: mapaRastreo });

        directionsService.route({
            origin: datosEntrega.repartidor,
            destination: datosEntrega.entrega,
            travelMode: 'DRIVING'
        }, (result, status) => {
            if (status === 'OK') {
                directionsRenderer.setDirections(result);
            }
        });

        // Auto-actualizar cada 5 segundos
        setInterval(actualizarPosicionRepartidor, 5000);
    }

    function actualizarPosicionRepartidor() {
        // Simular movimiento del repartidor
        const velocidad = 0.0001; // Aumentar lat/lng gradualmente
        datosEntrega.repartidor.lat += (Math.random() - 0.5) * velocidad;
        datosEntrega.repartidor.lng += (Math.random() - 0.5) * velocidad;

        marcadorRepartidor.setPosition(datosEntrega.repartidor);

        // Actualizar vista del mapa
        const centroMapa = {
            lat: (datosEntrega.repartidor.lat + datosEntrega.entrega.lat) / 2,
            lng: (datosEntrega.repartidor.lng + datosEntrega.entrega.lng) / 2
        };
        mapaRastreo.setCenter(centroMapa);
    }

    window.onload = inicializarMapaRastreo;
</script>

</body>
</html>
