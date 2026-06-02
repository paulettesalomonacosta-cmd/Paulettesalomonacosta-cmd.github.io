<?php
session_start();

if(!isset($_SESSION['idorden'])) {
    header("Location: index.php");
    exit();
}

$idorden = $_SESSION['idorden'];
$codigo_barras = $_SESSION['codigo_barras'];
$metodo_pago = $_SESSION['metodo_pago'];
$tienda_cercana = isset($_SESSION['tienda_cercana']) ? $_SESSION['tienda_cercana'] : '';
$total_compra = $_SESSION['total_compra'];
$detalles_compra = $_SESSION['detalles_compra'];
$direccion_entrega = isset($_SESSION['direccion_entrega']) ? $_SESSION['direccion_entrega'] : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirmación de Compra - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<link rel="stylesheet" href="estilos.css">
<style>
    .confirmacion-container {
        max-width: 900px;
        margin: 30px auto;
        background: white;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .confirmacion-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .confirmacion-icon {
        font-size: 60px;
        color: #28a745;
        margin-bottom: 20px;
    }

    .confirmacion-header h2 {
        color: #28a745;
        font-size: 28px;
        margin-bottom: 10px;
    }

    .numero-orden {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 30px;
        border-left: 4px solid #000000;
    }

    .numero-orden-label {
        color: #666;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .numero-orden-valor {
        font-size: 24px;
        font-weight: bold;
        color: #000000;
        font-family: 'Courier New', monospace;
    }

    .metodo-info {
        background: #e8f4f8;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        border-left: 4px solid #17a2b8;
    }

    .metodo-info h3 {
        color: #000000;
        margin-top: 0;
    }

    .metodo-detalle {
        margin: 10px 0;
        color: #333;
    }

    .codigo-barras-container {
        text-align: center;
        background: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        margin: 30px 0;
    }

    .codigo-barras-label {
        font-size: 16px;
        font-weight: bold;
        color: #000000;
        margin-bottom: 20px;
    }

    #mapa-entrega {
        width: 100%;
        height: 360px;
        border-radius: 12px;
        margin-top: 20px;
        border: 1px solid #ccc;
    }

    .estado-entrega {
        background: #f1f9f4;
        border-left: 4px solid #28a745;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        color: #15472b;
    }

    .estado-entrega h3 {
        margin-top: 0;
        color: #0a3d2f;
    }

    .estado-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        font-size: 15px;
    }

    .estado-item i {
        color: #28a745;
    }

    .alerta.error {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        color: #721c24;
        padding: 16px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .codigo-barras-imagen {
        background: white;
        padding: 20px;
        border-radius: 5px;
        display: inline-block;
        margin-bottom: 15px;
    }

    .codigo-barras-numero {
        font-size: 20px;
        font-weight: bold;
        font-family: 'Courier New', monospace;
        color: #000000;
        background: white;
        padding: 15px;
        border-radius: 5px;
        display: inline-block;
        width: 100%;
        box-sizing: border-box;
        margin-top: 15px;
    }

    .instrucciones {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 20px;
        border-radius: 8px;
        margin: 30px 0;
    }

    .instrucciones h3 {
        color: #856404;
        margin-top: 0;
    }

    .instrucciones ol {
        color: #856404;
        margin: 10px 0 0 20px;
    }

    .instrucciones li {
        margin: 8px 0;
    }

    .resumen-compra {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 30px 0;
    }

    .resumen-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
    }

    .resumen-item:last-child {
        border-bottom: none;
    }

    .item-info {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .item-imagen {
        width: 60px;
        height: 60px;
        object-fit: contain;
        margin-right: 15px;
        background: white;
        padding: 5px;
        border-radius: 3px;
    }

    .item-detalles {
        flex: 1;
    }

    .item-nombre {
        font-weight: bold;
        color: #000000;
        margin-bottom: 5px;
    }

    .item-cantidad {
        font-size: 12px;
        color: #666;
    }

    .item-precio {
        text-align: right;
        font-weight: bold;
        color: #000000;
    }

    .resumen-total {
        padding-top: 15px;
        margin-top: 15px;
        border-top: 2px solid #000000;
        display: flex;
        justify-content: space-between;
        font-size: 18px;
        font-weight: bold;
        color: #000000;
    }

    .botones-confirmacion {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 30px;
    }

    .btn-imprimir, .btn-descargar, .btn-volver {
        padding: 12px 25px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-imprimir {
        background: #007bff;
        color: white;
    }

    .btn-imprimir:hover {
        background: #0056b3;
    }

    .btn-descargar {
        background: #6c757d;
        color: white;
    }

    .btn-descargar:hover {
        background: #545b62;
    }

    .btn-volver {
        background: #28a745;
        color: white;
    }

    .btn-volver:hover {
        background: #218838;
    }

    .alerta-exito {
        background: #d4edda;
        border-left: 4px solid #28a745;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        color: #155724;
    }

    @media print {
        .botones-confirmacion {
            display: none;
        }
        .confirmacion-container {
            box-shadow: none;
        }
    }
</style>
</head>
<body>

<div class="confirmacion-container">
    <!-- Encabezado -->
    <div class="confirmacion-header">
        <div class="confirmacion-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>¡Compra Confirmada!</h2>
        <p>Tu pedido ha sido procesado correctamente</p>
    </div>

    <!-- Alerta de éxito -->
    <div class="alerta-exito">
        <i class="fas fa-check-circle"></i> Tu compra ha sido registrada exitosamente. Guarda este número de orden.
    </div>

    <!-- Número de orden -->
    <div class="numero-orden">
        <div class="numero-orden-label">Número de Orden:</div>
        <div class="numero-orden-valor">#<?php echo str_pad($idorden, 6, '0', STR_PAD_LEFT); ?></div>
    </div>

    <!-- Información del método de pago -->
    <div class="metodo-info">
        <h3><i class="fas fa-<?php echo ($metodo_pago === 'tarjeta') ? 'credit-card' : 'store'; ?>"></i> 
            Método de Pago: <?php echo ($metodo_pago === 'tarjeta') ? 'Tarjeta de Crédito/Débito' : 'Pago en Tienda'; ?>
        </h3>

        <?php if($metodo_pago === 'tarjeta'): ?>
            <div class="metodo-detalle">
                <i class="fas fa-info-circle"></i> Tu pago con tarjeta será procesado en breve. Recibirás una confirmación por correo electrónico.
            </div>
        <?php else: ?>
            <div class="metodo-detalle">
                <i class="fas fa-map-marker-alt"></i> <strong>Tienda:</strong> <?php echo htmlspecialchars($tienda_cercana); ?>
            </div>
            <div class="metodo-detalle">
                <i class="fas fa-clock"></i> <strong>Plazo de Pago:</strong> 3 días hábiles desde la generación de este código
            </div>
        <?php endif; ?>
    </div>

    <?php if($metodo_pago === 'tarjeta' && $direccion_entrega): ?>
    <div class="estado-entrega">
        <h3><i class="fas fa-shipping-fast"></i> Seguimiento de entrega en camino</h3>
        <p>Tu pedido se encuentra en ruta desde <strong>Culiacán, Sinaloa</strong> hacia tu dirección seleccionada.</p>
        <div class="estado-item">
            <i class="fas fa-map-marker-alt"></i>
            Entrega a: <strong><?php echo htmlspecialchars($direccion_entrega['calle'] . ' ' . $direccion_entrega['numero'] . ', ' . $direccion_entrega['ciudad'] . ', ' . $direccion_entrega['estado']); ?></strong>
        </div>
        <div class="estado-item">
            <i class="fas fa-clock"></i>
            Tiempo estimado de llegada: <strong>22 minutos</strong>
        </div>
        <div class="estado-item">
            <i class="fas fa-route"></i>
            Estado: <strong id="estadoActual">Recogiendo en centro de distribución</strong>
        </div>
    </div>
    <div id="mapa-entrega"></div>
    <?php if(empty($direccion_entrega['latitud']) || empty($direccion_entrega['longitud'])): ?>
        <div class="alerta error">No se pudo cargar el mapa de seguimiento porque la dirección seleccionada no tiene coordenadas válidas. Vuelve a seleccionar la dirección desde el carrito o guarda una nueva ubicación en el mapa.</div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Código de barras para pago en tienda -->
    <?php if($metodo_pago === 'tienda'): ?>
    <div class="codigo-barras-container">
        <div class="codigo-barras-label">
            <i class="fas fa-barcode"></i> CÓDIGO DE PAGO - Presenta este código en la tienda
        </div>
        
        <!-- Generar código de barras visual -->
        <div class="codigo-barras-imagen">
            <svg id="barcode" width="300" height="100"></svg>
        </div>
        
        <!-- Número de código de barras -->
        <div class="codigo-barras-numero">
            <?php echo htmlspecialchars($codigo_barras); ?>
        </div>
    </div>

    <div class="instrucciones">
        <h3><i class="fas fa-list-ol"></i> Instrucciones para Pagar en Tienda:</h3>
        <ol>
            <li>Guarda o imprime este código de barras</li>
            <li>Acércate a la tienda seleccionada dentro de 3 días hábiles</li>
            <li>Presenta el código de barras en la caja</li>
            <li>Realiza el pago del monto: <strong>$<?php echo number_format($total_compra, 2); ?></strong></li>
            <li>Tu pedido será confirmado y procesado una vez realizado el pago</li>
            <li>Recibirás los detalles de envío en tu correo electrónico</li>
        </ol>
    </div>
    <?php endif; ?>

    <!-- Resumen de compra -->
    <div class="resumen-compra">
        <h3 style="color: #000000; margin-top: 0;">Resumen de tu Compra</h3>
        
        <?php foreach($detalles_compra as $item): ?>
        <div class="resumen-item">
            <div class="item-info">
                <?php if(!empty($item['imagen'])): ?>
                    <img src="<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>" class="item-imagen">
                <?php else: ?>
                    <div style="width: 60px; height: 60px; background: #ddd; margin-right: 15px; border-radius: 3px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-image" style="color: #999; font-size: 20px;"></i>
                    </div>
                <?php endif; ?>
                <div class="item-detalles">
                    <div class="item-nombre"><?php echo htmlspecialchars($item['nombre']); ?></div>
                    <div class="item-cantidad">Cantidad: <?php echo $item['cantidad']; ?> x $<?php echo number_format($item['precio'], 2); ?></div>
                </div>
            </div>
            <div class="item-precio">$<?php echo number_format($item['subtotal'], 2); ?></div>
        </div>
        <?php endforeach; ?>

        <div class="resumen-total">
            <span>Total:</span>
            <span style="color: #28a745;">$<?php echo number_format($total_compra, 2); ?></span>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="botones-confirmacion">
        <?php if($metodo_pago === 'tienda'): ?>
        <button class="btn-descargar" onclick="descargarCodigoBarras()">
            <i class="fas fa-download"></i> Descargar Código
        </button>
        <?php endif; ?>
        <button class="btn-imprimir" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Comprobante
        </button>
        <a href="index.php">
            <button class="btn-volver">
                <i class="fas fa-home"></i> Volver a la Tienda
            </button>
        </a>
    </div>
</div>

<script>
    // Generar código de barras visual (usando JsBarcode)
    <?php if($metodo_pago === 'tienda'): ?>
    // Cargar librería JsBarcode
    var script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
    script.onload = function() {
        JsBarcode("#barcode", "<?php echo htmlspecialchars($codigo_barras); ?>", {
            format: "CODE128",
            width: 2,
            height: 100,
            displayValue: false
        });
    };
    document.head.appendChild(script);

    function descargarCodigoBarras() {
        const svg = document.querySelector('#barcode svg');
        if(!svg) {
            alert('El código de barras aún se está generando. Intenta de nuevo en un momento.');
            return;
        }
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const svgData = new XMLSerializer().serializeToString(svg);
        const img = new Image();
        
        img.onload = function() {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0);
            
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = 'codigo_barras_<?php echo $codigo_barras; ?>.png';
            link.click();
        };
        
        img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
    }
    <?php endif; ?>
</script>

    <?php if($metodo_pago === 'tarjeta' && $direccion_entrega): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                var destinoLat = <?php echo floatval($direccion_entrega['latitud'] ?? 0); ?>;
                var destinoLng = <?php echo floatval($direccion_entrega['longitud'] ?? 0); ?>;
                if(!destinoLat || !destinoLng) {
                    var mapa = document.getElementById('mapa-entrega');
                    if(mapa) {
                        mapa.innerHTML = '<div style="padding:24px; text-align:center; color:#444;">No se pudo cargar el mapa. Comprueba que tu dirección tenga coordenadas válidas y vuelve a intentarlo.</div>';
                    }
                    return;
                }

                var origenLat = 24.805000;
                var origenLng = -107.400000;
                var map = L.map('mapa-entrega', { zoomControl: false }).setView([origenLat, origenLng], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var origenIcon = L.icon({
                iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-green.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            var destinoIcon = L.icon({
                iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-red.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            var origenMarker = L.marker([origenLat, origenLng], { icon: origenIcon }).addTo(map).bindPopup('Tienda de origen: Culiacán, Sinaloa');
            var destinoMarker = L.marker([destinoLat, destinoLng], { icon: destinoIcon }).addTo(map).bindPopup('Tu dirección de entrega').openPopup();

            var routeLine = L.polyline([[origenLat, origenLng], [destinoLat, destinoLng]], { color: '#1976d2', weight: 5, opacity: 0.75 }).addTo(map);
            map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });
            setTimeout(function() { map.invalidateSize(); }, 200);

            var courierIcon = L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            var courierMarker = L.marker([origenLat, origenLng], { icon: courierIcon }).addTo(map);

            // Calcular ETA basado en distancia (Haversine) y animar repartidor en tiempo real
            function haversine(lat1, lon1, lat2, lon2) {
                const toRad = Math.PI / 180;
                const dLat = (lat2 - lat1) * toRad;
                const dLon = (lon2 - lon1) * toRad;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1*toRad) * Math.cos(lat2*toRad) * Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                const R = 6371; // km
                return R * c;
            }

            var distanceKm = haversine(origenLat, origenLng, destinoLat, destinoLng);
            var speedKmh = 50; // velocidad promedio simulada más rápida (km/h)
            var totalTimeSec = Math.max(60, Math.round((distanceKm / speedKmh) * 3600)); // mínimo 1 minuto
            var intervalMs = 1000; // actualización cada segundo
            var steps = Math.max(10, Math.round(totalTimeSec / (intervalMs/1000)));
            var currentStep = 0;

            var estados = [
                'Recogiendo en centro de distribución',
                'En camino hacia tu dirección',
                'Casi llegando a tu domicilio'
            ];
            var estadoActual = document.getElementById('estadoActual');
            var etaElem = document.getElementById('etaEstimado');
            if(etaElem) {
                var minsInit = Math.ceil(totalTimeSec / 60);
                etaElem.textContent = minsInit + ' min';
            }

            function moverRepartidor() {
                if(currentStep >= steps) {
                    courierMarker.setLatLng([destinoLat, destinoLng]);
                    estadoActual.textContent = 'Tu pedido llegó a la dirección. ¡Listo para entregar!';
                    if(etaElem) etaElem.textContent = 'Llegó';
                    return;
                }

                var t = currentStep / steps;
                var nuevaLat = origenLat + (destinoLat - origenLat) * t;
                var nuevaLng = origenLng + (destinoLng - origenLng) * t;
                courierMarker.setLatLng([nuevaLat, nuevaLng]);

                if(t < 0.15) {
                    estadoActual.textContent = estados[0];
                } else if(t < 0.6) {
                    estadoActual.textContent = estados[1];
                } else {
                    estadoActual.textContent = estados[2];
                }

                // actualizar ETA restante
                var remainingSec = Math.max(0, totalTimeSec - currentStep);
                var mins = Math.floor(remainingSec / 60);
                var secs = remainingSec % 60;
                if(etaElem) etaElem.textContent = (mins > 0 ? mins + 'm ' : '') + ('0' + secs).slice(-2) + 's';

                currentStep++;
            }

            setInterval(moverRepartidor, intervalMs);
        } catch (error) {
            console.error('Error inicializando el mapa de seguimiento:', error);
            var mapa = document.getElementById('mapa-entrega');
            if(mapa) {
                mapa.innerHTML = '<div style="padding:24px; text-align:center; color:#444;">Ha ocurrido un error cargando el mapa de seguimiento. Recarga la página o intenta con otra dirección.</div>';
            }
        }
    });
    </script>
    <?php endif; ?>

</body>
</html>
