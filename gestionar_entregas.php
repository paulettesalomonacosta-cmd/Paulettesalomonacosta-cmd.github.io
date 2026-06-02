<?php
session_start();
include("conexion.php");

// Verificar que sea admin
if(!isset($_SESSION['admin_id'])) {
    die("Acceso denegado");
}

$mensaje = "";
$tipo_mensaje = "";

// Procesar cambio de estado si se envía por POST
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idorden = intval($_POST['idorden']);
    $nuevo_estado = $_POST['nuevo_estado'];
    
    $sql = "UPDATE ordenes SET estado_entrega = ? WHERE idorden = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $idorden);
    
    if($stmt->execute()) {
        $mensaje = "✓ Estado actualizado a: <strong>" . ucfirst(str_replace('_', ' ', $nuevo_estado)) . "</strong>";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "✗ Error al actualizar: " . $stmt->error;
        $tipo_mensaje = "error";
    }
    $stmt->close();
}

// Obtener todas las órdenes
$sql = "SELECT o.*, u.nombre FROM ordenes o LEFT JOIN usuarios u ON o.idusuario = u.idusuarios ORDER BY o.fecha_orden DESC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestionar Entregas - Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
    }
    
    .mensaje {
        padding: 15px 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .mensaje.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .mensaje.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    th {
        background: #000000;
        color: white;
        padding: 12px;
        text-align: left;
    }
    
    td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    
    tr:hover {
        background: #f5f5f5;
    }
    
    .form-grupo {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    select {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    button {
        background: #17a2b8;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    
    button:hover {
        background: #138496;
    }
    
    .estado-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }
    
    .estado-pendiente {
        background: #e2e3e5;
        color: #383d41;
    }
    
    .estado-en_ruta {
        background: #fff3cd;
        color: #856404;
    }
    
    .estado-entregado {
        background: #d4edda;
        color: #155724;
    }
    
    .volver {
        display: inline-block;
        margin-bottom: 20px;
        background: #666;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
    }
    
    .volver:hover {
        background: #555;
    }
</style>
</head>
<body>
    <div class="container">
        <a href="admin_panel.php" class="volver"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
        
        <h1 style="color: #000000; margin: 20px 0;">📦 Gestionar Entregas</h1>
        
        <?php if($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <?php if($resultado && $resultado->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Orden #</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado Pago</th>
                        <th>Estado Entrega</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($orden = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo str_pad($orden['idorden'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                        <td><?php echo htmlspecialchars($orden['nombre'] ?? 'N/A'); ?></td>
                        <td>$<?php echo number_format($orden['total'], 2); ?></td>
                        <td>
                            <span class="estado-badge" style="background: #e8f4f8; color: #0c5460;">
                                <?php echo ucfirst(str_replace('_', ' ', $orden['estado'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="estado-badge estado-<?php echo $orden['estado_entrega']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $orden['estado_entrega'])); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <div class="form-grupo">
                                    <input type="hidden" name="idorden" value="<?php echo $orden['idorden']; ?>">
                                    <select name="nuevo_estado">
                                        <option value="pendiente" <?php echo ($orden['estado_entrega'] === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="en_ruta" <?php echo ($orden['estado_entrega'] === 'en_ruta') ? 'selected' : ''; ?>>En Ruta</option>
                                        <option value="entregado" <?php echo ($orden['estado_entrega'] === 'entregado') ? 'selected' : ''; ?>>Entregado</option>
                                    </select>
                                    <button type="submit"><i class="fas fa-save"></i> Actualizar</button>
                                    <button type="button" onclick="abrirModalUbicacion(<?php echo $orden['idorden']; ?>)" style="background: #6c757d;">
                                        <i class="fas fa-map-marker-alt"></i> Ubicación
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #666;">No hay órdenes en el sistema</p>
        <?php endif; ?>
    </div>
    
    <!-- Modal para editar ubicación -->
    <div id="modalUbicacion" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow-y: auto;">
        <div style="background-color: white; margin: 50px auto; padding: 30px; border-radius: 10px; max-width: 600px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #000000;">Ubicación de Entrega</h3>
                <span onclick="cerrarModalUbicacion()" style="font-size: 28px; font-weight: bold; color: #aaa; cursor: pointer;">&times;</span>
            </div>
            <form id="formUbicacion" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <input type="hidden" id="idorden" name="idorden">
                
                <input type="text" id="calle" placeholder="Calle" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; grid-column: 1 / -1;">
                
                <input type="text" id="numero" placeholder="Número" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <input type="text" id="ciudad" placeholder="Ciudad" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                
                <input type="text" id="estado" placeholder="Estado/Provincia" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <input type="text" id="pais" placeholder="País" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                
                <input type="number" id="latitud" placeholder="Latitud (ej: -33.856159)" step="0.000001" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <input type="number" id="longitud" placeholder="Longitud (ej: -56.167758)" step="0.000001" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                
                <button type="button" onclick="guardarUbicacion()" style="grid-column: 1 / -1; background: #17a2b8; padding: 12px;">
                    <i class="fas fa-save"></i> Guardar Ubicación
                </button>
            </form>
        </div>
    </div>
    
    <script>
    function abrirModalUbicacion(idorden) {
        document.getElementById('idorden').value = idorden;
        
        // Cargar datos actuales
        fetch('actualizar_ubicacion_orden.php?idorden=' + idorden)
            .then(response => response.json())
            .then(data => {
                if(data.success && data.data) {
                    document.getElementById('calle').value = data.data.calle || '';
                    document.getElementById('numero').value = data.data.numero || '';
                    document.getElementById('ciudad').value = data.data.ciudad || '';
                    document.getElementById('estado').value = data.data.estado || '';
                    document.getElementById('pais').value = data.data.pais || '';
                    document.getElementById('latitud').value = data.data.latitud || '';
                    document.getElementById('longitud').value = data.data.longitud || '';
                }
                document.getElementById('modalUbicacion').style.display = 'flex';
            });
    }
    
    function cerrarModalUbicacion() {
        document.getElementById('modalUbicacion').style.display = 'none';
    }
    
    function guardarUbicacion() {
        const formData = new FormData();
        formData.append('idorden', document.getElementById('idorden').value);
        formData.append('calle', document.getElementById('calle').value);
        formData.append('numero', document.getElementById('numero').value);
        formData.append('ciudad', document.getElementById('ciudad').value);
        formData.append('estado', document.getElementById('estado').value);
        formData.append('pais', document.getElementById('pais').value);
        formData.append('latitud', document.getElementById('latitud').value);
        formData.append('longitud', document.getElementById('longitud').value);
        
        fetch('actualizar_ubicacion_orden.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                cerrarModalUbicacion();
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al guardar ubicación');
        });
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('modalUbicacion');
        if(event.target === modal) {
            modal.style.display = 'none';
        }
    }
    </script>

<?php
$conexion->close();
?>
