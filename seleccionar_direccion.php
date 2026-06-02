<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$idusuarios = $_SESSION['usuario_id'];

// Asegurar que la tabla de direcciones exista
$sql_create = "CREATE TABLE IF NOT EXISTS direcciones_entrega (
    iddireccion INT AUTO_INCREMENT PRIMARY KEY,
    idusuarios INT NOT NULL,
    calle VARCHAR(255) NOT NULL,
    numero VARCHAR(20),
    apartamento VARCHAR(20),
    ciudad VARCHAR(100) NOT NULL,
    estado VARCHAR(100),
    codigo_postal VARCHAR(20),
    pais VARCHAR(100),
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    referencia TEXT,
    es_predeterminada BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idusuarios) REFERENCES usuarios(idusuarios) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conexion->query($sql_create);

// Obtener direcciones guardadas del usuario
$sql_direcciones = "SELECT * FROM direcciones_entrega WHERE idusuarios = $idusuarios ORDER BY es_predeterminada DESC, fecha_creacion DESC";
$resultado_direcciones = $conexion->query($sql_direcciones);
$direcciones = [];
if($resultado_direcciones) {
    while($fila = $resultado_direcciones->fetch_assoc()) {
        $direcciones[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seleccionar Dirección de Entrega - MAXIMA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<link rel="stylesheet" href="estilos.css">
<style>
    .direccion-container {
        max-width: 1200px;
        margin: 30px auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        padding: 0 20px;
    }

    .direcciones-panel {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .direcciones-panel h2 {
        color: #000000;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .direccion-item {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .direccion-item:hover {
        border-color: #000000;
        box-shadow: 0 2px 8px rgba(0,43,91,0.2);
    }

    .direccion-item.activa {
        border-color: #28a745;
        background: #f0f8f0;
    }

    .direccion-info {
        font-size: 14px;
        color: #666;
    }

    .direccion-info strong {
        display: block;
        color: #000000;
        margin-bottom: 5px;
    }

    .btn-agregar-direccion {
        width: 100%;
        padding: 12px;
        background: #000000;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        margin-bottom: 20px;
        transition: all 0.3s;
    }

    .btn-agregar-direccion:hover {
        background: #000000;
    }

    .mapa-panel {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    #map {
        width: 100%;
        height: 400px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .direccion-entrada {
        margin-bottom: 20px;
    }

    .direccion-entrada label {
        display: block;
        margin-bottom: 5px;
        color: #000000;
        font-weight: 600;
    }

    .direccion-entrada input,
    .direccion-entrada textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-family: inherit;
    }

    .direccion-entrada textarea {
        resize: vertical;
        min-height: 60px;
    }

    .btn-guardar-direccion {
        width: 100%;
        padding: 12px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
    }

    .btn-guardar-direccion:hover {
        background: #218838;
    }

    .coordenadas-info {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        font-size: 12px;
        color: #666;
        margin-bottom: 15px;
    }

    .buscador-direccion {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .buscador-direccion input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .buscador-direccion button {
        padding: 10px 20px;
        background: #000000;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .buscador-direccion button:hover {
        background: #000000;
    }

    @media (max-width: 900px) {
        .direccion-container {
            grid-template-columns: 1fr;
        }
    }

    .marca-pin {
        width: 30px;
        height: 30px;
        background: red;
        border: 3px solid white;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
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
                <li><a href="perfil.php">👤 Mi Perfil</a></li>
                <li><a href="logout.php">🚪 Salir</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="direccion-container">
    <!-- Panel de direcciones guardadas -->
    <div class="direcciones-panel">
        <h2><i class="fas fa-map-marker-alt"></i> Mis Direcciones</h2>
        
        <button class="btn-agregar-direccion" onclick="mostrarNuevaDireccion()">
            <i class="fas fa-plus"></i> Agregar Nueva Dirección
        </button>

        <div id="lista-direcciones">
            <?php if(!empty($direcciones)): ?>
                <?php foreach($direcciones as $dir): ?>
                <div class="direccion-item <?php echo $dir['es_predeterminada'] ? 'activa' : ''; ?>" 
                     onclick="seleccionarDireccion(event, <?php echo $dir['iddireccion']; ?>, <?php echo $dir['latitud']; ?>, <?php echo $dir['longitud']; ?>)">
                    <div class="direccion-info">
                        <strong><?php echo htmlspecialchars($dir['calle'] . ' ' . $dir['numero']); ?></strong>
                        <div><?php echo htmlspecialchars($dir['apartamento'] ? 'Apt. ' . $dir['apartamento'] . ' - ' : ''); 
                            echo htmlspecialchars($dir['ciudad'] . ', ' . $dir['estado']); ?></div>
                        <div><?php echo htmlspecialchars($dir['codigo_postal'] . ' ' . $dir['pais']); ?></div>
                        <?php if($dir['referencia']): ?>
                        <div><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($dir['referencia']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 20px;">No tienes direcciones guardadas. Agrega una nueva.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panel del mapa -->
    <div class="mapa-panel">
        <h2><i class="fas fa-map"></i> Seleccionar Ubicación</h2>

        <div class="buscador-direccion">
            <input type="text" id="buscar-direccion" placeholder="Buscar dirección...">
            <button onclick="buscarDireccion()"><i class="fas fa-search"></i></button>
        </div>

        <div id="map"></div>

        <div class="coordenadas-info" id="coordenadas-info">
            Haz clic en el mapa para seleccionar tu ubicación
        </div>

        <form id="form-nueva-direccion" style="display: none;">
            <div class="direccion-entrada">
                <label><i class="fas fa-road"></i> Calle</label>
                <input type="text" id="calle" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="direccion-entrada">
                    <label><i class="fas fa-building"></i> Número</label>
                    <input type="text" id="numero" required>
                </div>
                <div class="direccion-entrada">
                    <label><i class="fas fa-door-open"></i> Apartamento (Opt.)</label>
                    <input type="text" id="apartamento">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="direccion-entrada">
                    <label><i class="fas fa-city"></i> Ciudad</label>
                    <input type="text" id="ciudad" required>
                </div>
                <div class="direccion-entrada">
                    <label><i class="fas fa-map-signs"></i> Estado</label>
                    <input type="text" id="estado" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="direccion-entrada">
                    <label><i class="fas fa-mail-bulk"></i> Código Postal</label>
                    <input type="text" id="codigo_postal" required>
                </div>
                <div class="direccion-entrada">
                    <label><i class="fas fa-globe"></i> País</label>
                    <input type="text" id="pais" value="Colombia" required>
                </div>
            </div>

            <div class="direccion-entrada">
                <label><i class="fas fa-sticky-note"></i> Referencia (Ej: Casa blanca con puerta azul)</label>
                <textarea id="referencia"></textarea>
            </div>

            <button type="button" class="btn-guardar-direccion" onclick="guardarDireccion()">
                <i class="fas fa-check"></i> Guardar Dirección
            </button>
        </form>

        <button class="btn-guardar-direccion" id="btn-confirmar-ubicacion" style="display: none; background: #ff5500;" onclick="confirmarUbicacion()">
            <i class="fas fa-check"></i> Confirmar Esta Ubicación
        </button>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
    let mapa;
    let marcador;
    let latitud = -3.745404;
    let longitud = -38.523659;
    let direccionActualId = null;

    function inicializarMapa() {
        console.log('Leaflet: inicializarMapa start');
        const ubicacionInicial = [latitud, longitud];

        try {
            mapa = L.map('map').setView(ubicacionInicial, 15);
        } catch (err) {
            console.error('Leaflet init error', err);
            alert('Error inicializando el mapa. Revisa la consola del navegador.');
            return;
        }

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(mapa);

        marcador = L.marker(ubicacionInicial, { draggable: true }).addTo(mapa);
        marcador.bindPopup('Tu ubicación de entrega').openPopup();

        mapa.on('click', function(event) {
            colocarMarcador(event.latlng);
        });

        marcador.on('dragend', function(event) {
            const position = event.target.getLatLng();
            latitud = position.lat;
            longitud = position.lng;
            actualizarCoordenadas();
            llenarDireccion();
        });

        actualizarCoordenadas();
        llenarDireccion();
    }

    function colocarMarcador(ubicacion) {
        latitud = ubicacion.lat;
        longitud = ubicacion.lng;

        marcador.setLatLng(ubicacion);
        mapa.setView(ubicacion, 15);

        actualizarCoordenadas();
        llenarDireccion();
    }

    function actualizarCoordenadas() {
        document.getElementById('coordenadas-info').innerHTML = 
            `<strong>📍 Ubicación seleccionada:</strong><br>Lat: ${latitud.toFixed(6)}, Lng: ${longitud.toFixed(6)}`;
    }

    function buscarDireccion() {
        const busqueda = document.getElementById('buscar-direccion').value;
        if(!busqueda) return;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(busqueda)}`)
            .then(response => response.json())
            .then(results => {
                if(results.length === 0) {
                    alert('No se encontró la dirección');
                    return;
                }
                const location = results[0];
                colocarMarcador({ lat: parseFloat(location.lat), lng: parseFloat(location.lon) });
            })
            .catch(() => {
                alert('Error buscando la dirección');
            });
    }

    function llenarDireccion() {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitud}&lon=${longitud}&addressdetails=1`) 
            .then(response => response.json())
            .then(data => {
                if(!data || !data.address) {
                    return;
                }

                document.getElementById('form-nueva-direccion').style.display = 'block';
                document.getElementById('btn-confirmar-ubicacion').style.display = 'block';

                const address = data.address;
                document.getElementById('calle').value = address.road || address.pedestrian || address.house_number || '';
                document.getElementById('numero').value = address.house_number || '';
                document.getElementById('ciudad').value = address.city || address.town || address.village || address.county || '';
                document.getElementById('estado').value = address.state || address.region || '';
                document.getElementById('codigo_postal').value = address.postcode || '';
                document.getElementById('pais').value = address.country || 'Colombia';
            })
            .catch(() => {
                console.warn('No se pudo obtener dirección inversa');
            });
    }

    function seleccionarDireccion(event, iddireccion, lat, lng) {
        direccionActualId = iddireccion;
        colocarMarcador({ lat: parseFloat(lat), lng: parseFloat(lng) });

        document.querySelectorAll('.direccion-item').forEach(item => item.classList.remove('activa'));
        event.currentTarget.classList.add('activa');

        document.getElementById('form-nueva-direccion').style.display = 'none';
        document.getElementById('btn-confirmar-ubicacion').style.display = 'none';
    }

    function mostrarNuevaDireccion() {
        direccionActualId = null;
        document.getElementById('form-nueva-direccion').style.display = 'block';
        document.getElementById('btn-confirmar-ubicacion').style.display = 'block';
        document.querySelectorAll('.direccion-item').forEach(item => item.classList.remove('activa'));
    }

    function guardarDireccion() {
        const datos = {
            calle: document.getElementById('calle').value,
            numero: document.getElementById('numero').value,
            apartamento: document.getElementById('apartamento').value,
            ciudad: document.getElementById('ciudad').value,
            estado: document.getElementById('estado').value,
            codigo_postal: document.getElementById('codigo_postal').value,
            pais: document.getElementById('pais').value,
            referencia: document.getElementById('referencia').value,
            latitud: latitud,
            longitud: longitud
        };

        fetch('guardar_direccion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(r => r.json())
        .then(data => {
            if(data.éxito) {
                alert('✅ Dirección guardada correctamente');
                location.reload();
            } else {
                alert('❌ Error: ' + data.mensaje);
            }
        });
    }

    function confirmarUbicacion() {
        if(!direccionActualId) {
            alert('Por favor guarda la dirección primero');
            return;
        }

        window.location.href = 'metodos_pago.php?iddireccion=' + direccionActualId;
    }

    window.onload = inicializarMapa;

    document.getElementById('buscar-direccion').addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            buscarDireccion();
        }
    });
</script>
</body>
</html>
