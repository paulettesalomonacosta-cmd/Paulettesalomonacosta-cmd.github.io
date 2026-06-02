<?php
session_start();
include("conexion.php");

// Procesar cambio de estado
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $idorden = intval($_POST['idorden']);
    
    if($accion === 'marcar_pagado') {
        $sql = "UPDATE ordenes SET estado = 'pagado' WHERE idorden = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idorden);
        
        if($stmt->execute()) {
            $_SESSION['mensaje'] = "Orden marcada como pagada";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar la orden";
            $_SESSION['tipo_mensaje'] = "error";
        }
    }
    
    header("Location: ver_ordenes.php");
    exit();
}

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
<title>Gestor de Órdenes - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .gestor-container {
        max-width: 1400px;
        margin: 30px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .gestor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 20px;
    }

    .gestor-header h2 {
        color: #000000;
        margin: 0;
    }

    .estadisticas {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #000000 0%, #004a8f 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }

    .stat-card h3 {
        margin: 0 0 10px 0;
        font-size: 14px;
        font-weight: normal;
        opacity: 0.9;
    }

    .stat-card .numero {
        font-size: 32px;
        font-weight: bold;
    }

    .stat-card.pagadas {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-card.pendientes {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    }

    .tabla-ordenes-gestor {
        width: 100%;
        border-collapse: collapse;
    }

    .tabla-ordenes-gestor th {
        background: #000000;
        color: white;
        padding: 15px;
        text-align: left;
    }

    .tabla-ordenes-gestor td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
    }

    .tabla-ordenes-gestor tr:hover {
        background: #f9f9f9;
    }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }

    .badge-pendiente-pago-tarjeta {
        background: #fff3cd;
        color: #856404;
    }

    .badge-pendiente-pago-tienda {
        background: #cfe2ff;
        color: #084298;
    }

    .badge-pagado {
        background: #d1e7dd;
        color: #0f5132;
    }

    .badge-cancelado {
        background: #f8d7da;
        color: #842029;
    }

    .acciones-orden {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .btn-accion {
        padding: 6px 12px;
        border: none;
        border-radius: 3px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-pagado {
        background: #28a745;
        color: white;
    }

    .btn-pagado:hover {
        background: #218838;
    }

    .btn-detalles {
        background: #007bff;
        color: white;
    }

    .btn-detalles:hover {
        background: #0056b3;
    }

    .btn-descargar {
        background: #6c757d;
        color: white;
    }

    .btn-descargar:hover {
        background: #5a6268;
    }

    .mensaje {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        display: none;
    }

    .mensaje.success {
        background: #d4edda;
        border-left: 4px solid #28a745;
        color: #155724;
        display: block;
    }

    .mensaje.error {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        color: #721c24;
        display: block;
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

    @media (max-width: 768px) {
        .gestor-header {
            flex-direction: column;
            gap: 15px;
        }
        .estadisticas {
            grid-template-columns: 1fr 1fr;
        }
        .tabla-ordenes-gestor {
            font-size: 12px;
        }
        .tabla-ordenes-gestor td, .tabla-ordenes-gestor th {
            padding: 8px;
        }
    }
</style>
</head>
<body>

<div class="gestor-container">
    <div class="gestor-header">
        <h2><i class="fas fa-cogs"></i> Gestor de Órdenes de Compra</h2>
        <a href="admin_panel.php" style="color: #000000; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Volver a Admin
        </a>
    </div>

    <?php if(isset($_SESSION['mensaje'])): ?>
        <div class="mensaje <?php echo $_SESSION['tipo_mensaje']; ?>">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['mensaje']; ?>
        </div>
        <?php unset($_SESSION['mensaje']); unset($_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="estadisticas">
        <div class="stat-card">
            <h3>ÓRDENES TOTALES</h3>
            <div class="numero"><?php echo $resultado->num_rows; ?></div>
        </div>
        <div class="stat-card pagadas">
            <h3>ÓRDENES PAGADAS</h3>
            <div class="numero">
                <?php 
                $sql_pagadas = "SELECT COUNT(*) as total FROM ordenes WHERE estado = 'pagado'";
                $res_pagadas = $conexion->query($sql_pagadas);
                $row_pagadas = $res_pagadas->fetch_assoc();
                echo $row_pagadas['total'];
                ?>
            </div>
        </div>
        <div class="stat-card pendientes">
            <h3>PENDIENTES DE PAGO</h3>
            <div class="numero">
                <?php 
                $sql_pendientes = "SELECT COUNT(*) as total FROM ordenes WHERE estado LIKE 'pendiente%'";
                $res_pendientes = $conexion->query($sql_pendientes);
                $row_pendientes = $res_pendientes->fetch_assoc();
                echo $row_pendientes['total'];
                ?>
            </div>
        </div>
        <div class="stat-card">
            <h3>MONTO TOTAL</h3>
            <div class="numero">
                <?php 
                $sql_total = "SELECT SUM(total) as monto FROM ordenes WHERE estado = 'pagado'";
                $res_total = $conexion->query($sql_total);
                $row_total = $res_total->fetch_assoc();
                echo "$" . number_format($row_total['monto'] ?? 0, 2);
                ?>
            </div>
        </div>
    </div>

    <!-- Tabla de órdenes -->
    <?php if($resultado->num_rows > 0): ?>
        <table class="tabla-ordenes-gestor">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Items</th>
                    <th>Pago</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $resultado->data_seek(0);
                while($orden = $resultado->fetch_assoc()): 
                    $estado_clase = 'badge-' . str_replace(' ', '-', $orden['estado']);
                ?>
                <tr>
                    <td><strong>#<?php echo str_pad($orden['idorden'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                    <td><?php echo date('d/m/Y', strtotime($orden['fecha_orden'])); ?></td>
                    <td>ID: <?php echo $orden['idusuario']; ?></td>
                    <td><strong>$<?php echo number_format($orden['total'], 2); ?></strong></td>
                    <td><?php echo $orden['cantidad_items']; ?></td>
                    <td>
                        <?php if($orden['metodo_pago'] === 'tarjeta'): ?>
                            <i class="fas fa-credit-card"></i> Tarjeta
                        <?php else: ?>
                            <i class="fas fa-store"></i> Tienda
                        <?php endif; ?>
                    </td>
                    <td><span class="badge <?php echo $estado_clase; ?>"><?php echo ucfirst(str_replace('_', ' ', $orden['estado'])); ?></span></td>
                    <td>
                        <div class="acciones-orden">
                            <button class="btn-accion btn-detalles" onclick="verDetalles(<?php echo $orden['idorden']; ?>)">
                                <i class="fas fa-info-circle"></i> Ver
                            </button>
                            <?php if($orden['estado'] !== 'pagado'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="marcar_pagado">
                                <input type="hidden" name="idorden" value="<?php echo $orden['idorden']; ?>">
                                <button type="submit" class="btn-accion btn-pagado" onclick="return confirm('¿Marcar como pagada?')">
                                    <i class="fas fa-check"></i> Pagar
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999;">
            <i class="fas fa-inbox" style="font-size: 50px; color: #ddd; margin-bottom: 20px; display: block;"></i>
            <h3>No hay órdenes</h3>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para detalles -->
<div id="modalDetalles" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detalles de la Orden</h3>
            <span class="close" onclick="cerrarModal('modalDetalles')">&times;</span>
        </div>
        <div id="detalles-contenido"></div>
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

</body>
</html>

<?php
$conexion->close();
?>
