<?php
session_start();
include("conexion.php");

// Verificar que esté logueado
if (!isset($_SESSION['usuario_id'])) {
    echo '<div style="text-align: center; padding: 40px; color: #999;">';
    echo '<i class="fas fa-sign-in-alt" style="font-size: 48px; color: #ddd; display: block; margin-bottom: 15px;"></i>';
    echo '<p><strong>Debes estar logueado para ver tus pedidos</strong></p>';
    echo '</div>';
    exit();
}

$usuario_id = intval($_SESSION['usuario_id']);

// Obtener órdenes del usuario logueado
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM detalles_orden WHERE idorden = o.idorden) as cantidad_items
        FROM ordenes o 
        WHERE o.idusuario = ?
        ORDER BY o.fecha_orden DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    echo '<div style="text-align: center; padding: 40px; color: #999;">';
    echo '<i class="fas fa-box" style="font-size: 48px; color: #ddd; display: block; margin-bottom: 15px;"></i>';
    echo '<p><strong>No hay productos en seguimiento</strong></p>';
    echo '<p>Realiza una compra para ver el estado de tus pedidos aquí.</p>';
    echo '</div>';
    exit();
}
?>

<h2 style="color: #000000; margin-top: 0;">📦 Seguimiento de Pedidos</h2>
<p style="color: #666; margin-bottom: 20px;">Aquí puedes ver el estado y detalles de tus pedidos</p>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <thead>
        <tr style="background: #f5f5f5; border-bottom: 2px solid #000000;">
            <th style="padding: 12px; text-align: left; color: #000000;">Orden #</th>
            <th style="padding: 12px; text-align: left; color: #000000;">Fecha</th>
            <th style="padding: 12px; text-align: left; color: #000000;">Total</th>
            <th style="padding: 12px; text-align: left; color: #000000;">Items</th>
            <th style="padding: 12px; text-align: left; color: #000000;">Pago</th>
            <th style="padding: 12px; text-align: left; color: #000000;">Entrega</th>
            <th style="padding: 12px; text-align: center; color: #000000;">Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $estado_colores = [
            'pendiente_pago_tarjeta' => '#ff9800',
            'pendiente_pago_tienda' => '#ff9800',
            'pagado' => '#4caf50',
            'cancelado' => '#f44336',
            'entregado' => '#4caf50'
        ];
        
        $entrega_colores = [
            'pendiente' => '#e2e3e5',
            'en_ruta' => '#fff3cd',
            'entregado' => '#d4edda'
        ];
        
        while ($orden = $resultado->fetch_assoc()): 
            $estado = str_replace('_', ' ', ucfirst($orden['estado']));
            $color_pago = $estado_colores[$orden['estado']] ?? '#999';
            
            $estado_entrega = $orden['estado_entrega'] ?? 'pendiente';
            $color_entrega = $entrega_colores[$estado_entrega] ?? '#999';
            $texto_entrega = ucfirst(str_replace('_', ' ', $estado_entrega));
        ?>
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; font-weight: bold;">#<?php echo str_pad($orden['idorden'], 6, '0', STR_PAD_LEFT); ?></td>
            <td style="padding: 12px;"><?php echo date('d/m/Y H:i', strtotime($orden['fecha_orden'])); ?></td>
            <td style="padding: 12px; font-weight: bold; color: #000000;">$<?php echo number_format($orden['total'], 2); ?></td>
            <td style="padding: 12px;"><?php echo $orden['cantidad_items']; ?> items</td>
            <td style="padding: 12px;">
                <span style="background: <?php echo $color_pago; ?>; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px;">
                    <?php echo $estado; ?>
                </span>
            </td>
            <td style="padding: 12px;">
                <span style="background: <?php echo $color_entrega; ?>; color: #333; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                    <?php if($estado_entrega === 'en_ruta'): ?>
                        🚚 <?php echo $texto_entrega; ?>
                    <?php elseif($estado_entrega === 'entregado'): ?>
                        ✓ <?php echo $texto_entrega; ?>
                    <?php else: ?>
                        ⏳ <?php echo $texto_entrega; ?>
                    <?php endif; ?>
                </span>
            </td>
            <td style="padding: 12px; text-align: center;">
                <button onclick="verDetalles(<?php echo $orden['idorden']; ?>)" style="background: #000000; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; margin-right: 5px;">
                    <i class="fas fa-eye"></i> Ver
                </button>
                <?php if($estado_entrega === 'entregado'): ?>
                    <button onclick="eliminarSeguimiento(<?php echo $orden['idorden']; ?>)" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer;">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Modal para detalles de orden -->
<div id="modalDetalles" style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow: auto;">
    <div style="background-color: white; margin: 50px auto; padding: 30px; border-radius: 10px; max-width: 700px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
            <h3 style="margin: 0; color: #000000;">Detalles de la Orden</h3>
            <span onclick="cerrarModal('modalDetalles')" style="font-size: 28px; font-weight: bold; color: #aaa; cursor: pointer;">&times;</span>
        </div>
        <div id="detalles-contenido" style="max-height: 70vh; overflow-y: auto;"></div>
    </div>
</div>

<script>
function verDetalles(idorden) {
    fetch('get_detalle_orden.php?id=' + idorden)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar los detalles');
            }
            return response.text();
        })
        .then(data => {
            document.getElementById('detalles-contenido').innerHTML = data;
            document.getElementById('modalDetalles').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al cargar detalles: ' + error.message);
        });
}

function eliminarSeguimiento(idorden) {
    if(!confirm('¿Eliminar este seguimiento? No podrás recuperarlo.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('idorden', idorden);
    formData.append('accion', 'eliminar');
    
    fetch('eliminar_seguimiento.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al eliminar seguimiento: ' + error.message);
    });
}

function cerrarModal(idModal) {
    document.getElementById(idModal).style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('modalDetalles');
    if(event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>
}
</script>

<?php
$conexion->close();
?>
