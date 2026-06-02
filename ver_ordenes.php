<?php
session_start();
include("conexion.php");

// Obtener todas las órdenes
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM detalles_orden WHERE idorden = o.idorden) as cantidad_items
        FROM ordenes o 
        ORDER BY o.fecha_orden DESC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Órdenes de Compra - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .ordenes-container {
        max-width: 1200px;
        margin: 30px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .ordenes-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .ordenes-header h2 {
        color: #000000;
        margin: 0;
    }

    .btn-volver {
        background: #666;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-volver:hover {
        background: #555;
    }

    .tabla-ordenes {
        width: 100%;
        border-collapse: collapse;
    }

    .tabla-ordenes th {
        background: #000000;
        color: white;
        padding: 15px;
        text-align: left;
    }

    .tabla-ordenes td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
    }

    .tabla-ordenes tr:hover {
        background: #f9f9f9;
    }

    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }

    .badge-pendiente-tarjeta {
        background: #ffc107;
        color: #333;
    }

    .badge-pendiente-tienda {
        background: #17a2b8;
        color: white;
    }

    .badge-pagado {
        background: #28a745;
        color: white;
    }

    .badge-cancelado {
        background: #dc3545;
        color: white;
    }

    .btn-detalle {
        background: #007bff;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
    }

    .btn-detalle:hover {
        background: #0056b3;
    }

    .btn-codigo-barras {
        background: #6c757d;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
    }

    .btn-codigo-barras:hover {
        background: #5a6268;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 30px;
        border-radius: 10px;
        max-width: 600px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 15px;
    }

    .modal-header h3 {
        margin: 0;
        color: #000000;
    }

    .close {
        font-size: 28px;
        font-weight: bold;
        color: #aaa;
        cursor: pointer;
    }

    .close:hover {
        color: #000;
    }

    .detalle-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .detalle-item:last-child {
        border-bottom: none;
    }

    .codigo-barras-display {
        text-align: center;
        margin: 20px 0;
    }

    .codigo-barras-numero {
        background: #f8f9fa;
        padding: 15px;
        font-family: 'Courier New', monospace;
        font-size: 18px;
        font-weight: bold;
        border-radius: 5px;
        word-break: break-all;
    }

    .tabla-vacia {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .tabla-vacia i {
        font-size: 50px;
        color: #ddd;
        margin-bottom: 20px;
    }

    .filtros {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
    }

    .filtros select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
</style>
</head>
<body>

<div class="ordenes-container">
    <div class="ordenes-header">
        <h2><i class="fas fa-list"></i> Panel de Órdenes de Compra</h2>
        <a href="admin_panel.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <div class="filtros">
        <select id="filtro-estado" onchange="filtrarOrdenes()">
            <option value="">Todos los estados</option>
            <option value="pendiente_pago_tarjeta">Pendiente Tarjeta</option>
            <option value="pendiente_pago_tienda">Pendiente Tienda</option>
            <option value="pagado">Pagado</option>
            <option value="cancelado">Cancelado</option>
        </select>
    </div>

    <?php if($resultado->num_rows > 0): ?>
        <table class="tabla-ordenes">
            <thead>
                <tr>
                    <th>Orden #</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Items</th>
                    <th>Método de Pago</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($orden = $resultado->fetch_assoc()): 
                    $estado_clase = 'badge-' . str_replace(' ', '-', $orden['estado']);
                ?>
                <tr>
                    <td>
                        <strong>#<?php echo str_pad($orden['idorden'], 6, '0', STR_PAD_LEFT); ?></strong>
                    </td>
                    <td>
                        <?php echo date('d/m/Y H:i', strtotime($orden['fecha_orden'])); ?>
                    </td>
                    <td>
                        <strong>$<?php echo number_format($orden['total'], 2); ?></strong>
                    </td>
                    <td>
                        <?php echo $orden['cantidad_items']; ?> producto(s)
                    </td>
                    <td>
                        <?php if($orden['metodo_pago'] === 'tarjeta'): ?>
                            <i class="fas fa-credit-card"></i> Tarjeta ****<?php echo substr($orden['numero_tarjeta'], -4); ?>
                        <?php else: ?>
                            <i class="fas fa-store"></i> Tienda
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $estado_clase; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $orden['estado'])); ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-detalle" onclick="verDetalles(<?php echo $orden['idorden']; ?>)">
                            <i class="fas fa-info-circle"></i> Detalles
                        </button>
                        <?php if($orden['metodo_pago'] === 'tienda' && !empty($orden['codigo_barras'])): ?>
                        <button class="btn-codigo-barras" onclick="verCodigoBarras('<?php echo htmlspecialchars($orden['codigo_barras']); ?>')">
                            <i class="fas fa-barcode"></i> Código
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="tabla-vacia">
            <i class="fas fa-inbox"></i>
            <h3>No hay órdenes registradas</h3>
            <p>Las órdenes aparecerán aquí cuando los clientes realicen compras</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para detalles de orden -->
<div id="modalDetalles" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detalles de la Orden</h3>
            <span class="close" onclick="cerrarModal('modalDetalles')">&times;</span>
        </div>
        <div id="detalles-contenido"></div>
    </div>
</div>

<!-- Modal para código de barras -->
<div id="modalCodigoBarras" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Código de Barras de Pago</h3>
            <span class="close" onclick="cerrarModal('modalCodigoBarras')">&times;</span>
        </div>
        <div class="codigo-barras-display">
            <div class="codigo-barras-numero" id="codigo-numero"></div>
            <svg id="barcode-svg" width="100%" height="100" style="margin: 20px 0;"></svg>
        </div>
    </div>
</div>

<script>
    function verDetalles(idorden) {
        fetch('get_detalle_orden.php?id=' + idorden)
            .then(response => response.text())
            .then(data => {
                document.getElementById('detalles-contenido').innerHTML = data;
                document.getElementById('modalDetalles').style.display = 'block';
            });
    }

    function verCodigoBarras(codigo) {
        document.getElementById('codigo-numero').textContent = codigo;
        
        // Cargar librería JsBarcode
        if(typeof JsBarcode === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
            script.onload = function() {
                JsBarcode("#barcode-svg", codigo, {
                    format: "CODE128",
                    width: 2,
                    height: 100
                });
            };
            document.head.appendChild(script);
        } else {
            JsBarcode("#barcode-svg", codigo, {
                format: "CODE128",
                width: 2,
                height: 100
            });
        }
        
        document.getElementById('modalCodigoBarras').style.display = 'block';
    }

    function cerrarModal(idModal) {
        document.getElementById(idModal).style.display = 'none';
    }

    window.onclick = function(event) {
        const modales = ['modalDetalles', 'modalCodigoBarras'];
        modales.forEach(id => {
            const modal = document.getElementById(id);
            if(event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    function filtrarOrdenes() {
        const estado = document.getElementById('filtro-estado').value;
        const filas = document.querySelectorAll('.tabla-ordenes tbody tr');
        
        filas.forEach(fila => {
            if(estado === '') {
                fila.style.display = '';
            } else {
                const badgeTexto = fila.querySelector('.badge').textContent;
                if(badgeTexto.toLowerCase().includes(estado.replace(/_/g, ' '))) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            }
        });
    }
</script>

</body>
</html>

<?php
$conexion->close();
?>
