<?php
session_start();
include("conexion.php");

if(!isset($_GET['id'])) {
    die("ID de orden no especificado");
}

$idorden = intval($_GET['id']);
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;

// Obtener información de la orden
$sql_orden = "SELECT o.*, 
              d.calle, d.numero, d.apartamento, d.ciudad, d.estado, d.codigo_postal, 
              d.pais, d.latitud, d.longitud, d.referencia
              FROM ordenes o 
              LEFT JOIN direcciones_entrega d ON o.idusuario = d.idusuarios
              WHERE o.idorden = ? AND o.idusuario = ?";

$stmt = $conexion->prepare($sql_orden);
$stmt->bind_param("ii", $idorden, $usuario_id);
$stmt->execute();
$resultado_orden = $stmt->get_result();
$orden = $resultado_orden->fetch_assoc();
$stmt->close();

if(!$orden) {
    die("Orden no encontrada");
}

// Obtener detalles de la orden
$sql_detalles = "SELECT * FROM detalles_orden WHERE idorden = ?";
$stmt_detalles = $conexion->prepare($sql_detalles);
$stmt_detalles->bind_param("i", $idorden);
$stmt_detalles->execute();
$resultado_detalles = $stmt_detalles->get_result();
?>

<div style="background: white; padding: 0; border-radius: 8px; font-family: Arial, sans-serif;">
    <!-- Encabezado con estado -->
    <div style="background: linear-gradient(135deg, #000000 0%, #000000 100%); color: white; padding: 30px; border-radius: 8px 8px 0 0; text-align: center;">
        <h3 style="margin: 0 0 10px 0; font-size: 22px;">📦 Seguimiento de Pedido</h3>
        <p style="margin: 5px 0; font-size: 14px; opacity: 0.9;">Orden #<?php echo str_pad($orden['idorden'], 6, '0', STR_PAD_LEFT); ?></p>
        <p style="margin: 5px 0; font-size: 13px; opacity: 0.8;">Fecha: <?php echo date('d/m/Y H:i', strtotime($orden['fecha_orden'])); ?></p>
    </div>

    <!-- Timeline de Entrega Visual -->
    <div style="padding: 30px; background: #f8f9fa;">
        <h4 style="color: #000000; margin: 0 0 20px 0; font-size: 16px;">Estado de tu Pedido</h4>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 30px 0;">
            <!-- Paso 1: Confirmado -->
            <div style="text-align: center; flex: 1;">
                <div style="background: #4caf50; width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    ✓
                </div>
                <p style="margin: 0; font-weight: bold; color: #333; font-size: 14px;">Confirmado</p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Orden recibida</p>
            </div>
            
            <!-- Línea conector -->
            <div style="flex: 1; height: 3px; background: #ddd; margin: 0 10px 30px 10px;"></div>
            
            <!-- Paso 2: En Ruta -->
            <div style="text-align: center; flex: 1;">
                <?php if($orden['estado_entrega'] === 'en_ruta' || $orden['estado_entrega'] === 'entregado'): ?>
                    <div style="background: #ff9800; width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        🚚
                    </div>
                    <p style="margin: 0; font-weight: bold; color: #333; font-size: 14px;">En Ruta</p>
                <?php else: ?>
                    <div style="background: #e0e0e0; width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        🚚
                    </div>
                    <p style="margin: 0; font-weight: bold; color: #999; font-size: 14px;">Preparando</p>
                <?php endif; ?>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Enviando...</p>
            </div>
            
            <!-- Línea conector -->
            <div style="flex: 1; height: 3px; background: <?php echo ($orden['estado_entrega'] === 'entregado') ? '#4caf50' : '#ddd'; ?>; margin: 0 10px 30px 10px;"></div>
            
            <!-- Paso 3: Entregado -->
            <div style="text-align: center; flex: 1;">
                <?php if($orden['estado_entrega'] === 'entregado'): ?>
                    <div style="background: #4caf50; width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        ✓
                    </div>
                    <p style="margin: 0; font-weight: bold; color: #333; font-size: 14px;">Entregado</p>
                <?php else: ?>
                    <div style="background: #e0e0e0; width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        ✓
                    </div>
                    <p style="margin: 0; font-weight: bold; color: #999; font-size: 14px;">Próximamente</p>
                <?php endif; ?>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">En tu puerta</p>
            </div>
        </div>
    </div>

    <!-- Información de Ubicación y Mapa -->
    <?php 
    $fecha_orden = strtotime($orden['fecha_orden']);
    $ahora = time();
    $dias_transcurridos = floor(($ahora - $fecha_orden) / 86400);
    
    if($orden['estado_entrega'] === 'entregado') {
        $tiempo_estimado = '✓ Entregado';
        $dias_restantes = 0;
        $color_estado = '#4caf50';
        $icono_estado = '✓';
    } else if($orden['estado_entrega'] === 'en_ruta') {
        $dias_restantes = max(1, 2 - $dias_transcurridos);
        $tiempo_estimado = $dias_restantes . ' día(s)';
        $color_estado = '#ff9800';
        $icono_estado = '🚚';
    } else {
        $dias_restantes = max(1, 3 - $dias_transcurridos);
        $tiempo_estimado = $dias_restantes . ' día(s)';
        $color_estado = '#2196F3';
        $icono_estado = '⏳';
    }
    ?>

    <?php if($orden['latitud'] && $orden['longitud']): ?>
    <div style="padding: 20px;">
        <!-- Mapa -->
        <div id="mapa-entrega" style="width: 100%; height: 400px; border-radius: 8px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: #eee;"></div>

        <!-- Información de Entrega -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
            <!-- Tiempo Estimado -->
            <div style="background: <?php echo $color_estado; ?>; color: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <p style="margin: 0; font-size: 28px; font-weight: bold;"><?php echo $icono_estado; ?></p>
                <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: bold;"><?php echo $tiempo_estimado; ?></p>
                <p style="margin: 5px 0 0 0; font-size: 13px; opacity: 0.9;">Tiempo estimado</p>
            </div>

            <!-- Estado Actual -->
            <div style="background: #000000; color: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <p style="margin: 0; font-size: 13px; opacity: 0.9;">Estado Actual</p>
                <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: bold;">
                    <?php if($orden['estado_entrega'] === 'entregado'): ?>
                        ✓ ENTREGADO
                    <?php elseif($orden['estado_entrega'] === 'en_ruta'): ?>
                        🚚 EN CAMINO
                    <?php else: ?>
                        ⏳ PREPARANDO
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Dirección de Entrega -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #000000; margin-bottom: 20px;">
            <p style="margin: 0 0 12px 0; color: #000000; font-weight: bold; font-size: 14px;">📍 Dirección de Entrega</p>
            <p style="margin: 0; color: #333; font-size: 14px; line-height: 1.6;">
                <strong><?php echo htmlspecialchars($orden['calle'] . ' ' . $orden['numero']); ?></strong><br>
                <?php if($orden['apartamento']) echo htmlspecialchars($orden['apartamento']) . '<br>'; ?>
                <?php echo htmlspecialchars($orden['ciudad']); ?>
                <?php if($orden['estado']) echo ', ' . htmlspecialchars($orden['estado']); ?>
                <?php if($orden['codigo_postal']) echo ' ' . htmlspecialchars($orden['codigo_postal']); ?><br>
                <?php if($orden['pais']) echo htmlspecialchars($orden['pais']); ?>
            </p>
            <?php if($orden['latitud'] && $orden['longitud']): ?>
                <p style="margin: 12px 0 0 0; color: #17a2b8; font-size: 12px; background: #e8f4f8; padding: 8px; border-radius: 4px;"><strong>📍 GPS:</strong> <?php echo number_format($orden['latitud'], 6); ?>, <?php echo number_format($orden['longitud'], 6); ?></p>
            <?php endif; ?>
        </div>

        <!-- Botón de Confirmación -->
        <?php if($orden['estado_entrega'] === 'en_ruta'): ?>
        <div style="text-align: center; margin-bottom: 20px;">
            <button onclick="confirmarEntrega(<?php echo $orden['idorden']; ?>)" style="background: #28a745; color: white; border: none; padding: 15px 40px; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: bold; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <i class="fas fa-thumbs-up"></i> ¡Muchas gracias! Ya llegó
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Sin Mapa - Solo Información -->
    <div style="padding: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
            <!-- Tiempo Estimado -->
            <div style="background: <?php echo $color_estado; ?>; color: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <p style="margin: 0; font-size: 28px; font-weight: bold;"><?php echo $icono_estado; ?></p>
                <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: bold;"><?php echo $tiempo_estimado; ?></p>
                <p style="margin: 5px 0 0 0; font-size: 13px; opacity: 0.9;">Tiempo estimado</p>
            </div>

            <!-- Estado Actual -->
            <div style="background: #000000; color: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <p style="margin: 0; font-size: 13px; opacity: 0.9;">Estado Actual</p>
                <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: bold;">
                    <?php if($orden['estado_entrega'] === 'entregado'): ?>
                        ✓ ENTREGADO
                    <?php elseif($orden['estado_entrega'] === 'en_ruta'): ?>
                        🚚 EN CAMINO
                    <?php else: ?>
                        ⏳ PREPARANDO
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Aviso de Carga del Mapa -->
        <div style="background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
            <p style="margin: 0; color: #856404; font-weight: bold; font-size: 14px;"><i class="fas fa-info-circle"></i> Mapa cargando...</p>
            <p style="margin: 8px 0 0 0; color: #856404; font-size: 13px;">El mapa de seguimiento en tiempo real se mostrará una vez que tu pedido salga del almacén.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Productos -->
    <div style="padding: 20px; border-top: 2px solid #f0f0f0;">
        <h4 style="color: #000000; margin: 0 0 15px 0;">📦 Productos en tu Pedido</h4>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="padding: 12px; text-align: left; color: #333; font-size: 13px; border-bottom: 2px solid #ddd;">Producto</th>
                    <th style="padding: 12px; text-align: center; color: #333; font-size: 13px; border-bottom: 2px solid #ddd;">Cant.</th>
                    <th style="padding: 12px; text-align: right; color: #333; font-size: 13px; border-bottom: 2px solid #ddd;">Precio</th>
                    <th style="padding: 12px; text-align: right; color: #333; font-size: 13px; border-bottom: 2px solid #ddd;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($detalle = $resultado_detalles->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; color: #333; font-size: 13px;"><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
                    <td style="padding: 12px; text-align: center; color: #333; font-size: 13px;"><?php echo $detalle['cantidad']; ?></td>
                    <td style="padding: 12px; text-align: right; color: #333; font-size: 13px;">$<?php echo number_format($detalle['precio'], 2); ?></td>
                    <td style="padding: 12px; text-align: right; color: #000000; font-weight: bold; font-size: 13px;">$<?php echo number_format($detalle['subtotal'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Total y Método de Pago -->
    <div style="padding: 20px; background: #f8f9fa; border-top: 2px solid #f0f0f0;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <p style="margin: 0; color: #666; font-size: 13px;">Método de Pago</p>
                <p style="margin: 5px 0 0 0; color: #000000; font-weight: bold; font-size: 14px;"><?php echo ucfirst(str_replace('_', ' ', $orden['metodo_pago'])); ?></p>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0; color: #666; font-size: 13px;">Total de la Orden</p>
                <p style="margin: 5px 0 0 0; color: #000000; font-weight: bold; font-size: 18px;">$<?php echo number_format($orden['total'], 2); ?></p>
            </div>
        </div>
    </div>
</div>

    <!-- Timeline de Entrega -->
    <div style="margin-bottom: 30px;">
        <h4 style="color: #000000; margin-bottom: 20px;">📦 Estado de tu Pedido</h4>
        
        <div style="display: flex; align-items: center; margin-bottom: 25px;">
            <!-- Paso 1: Confirmado -->
            <div style="text-align: center; flex: 1;">
                <div style="background: #4caf50; color: white; width: 50px; height: 50px; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    ✓
                </div>
                <p style="margin: 0; font-weight: bold; color: #333;">Confirmado</p>
                <p style="margin: 3px 0 0 0; font-size: 12px; color: #666;">Orden recibida</p>
            </div>
            
            <!-- Línea conector -->
            <div style="flex: 1; height: 3px; background: #ddd; margin: 0 10px 30px 10px;"></div>
            
            <!-- Paso 2: En Ruta -->
            <div style="text-align: center; flex: 1;">
                <?php if($orden['estado_entrega'] === 'en_ruta' || $orden['estado_entrega'] === 'entregado'): ?>
                    <div style="background: #ff9800; color: white; width: 50px; height: 50px; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        🚚
                    </div>
                    <p style="margin: 0; font-weight: bold; color: #333;">En Ruta</p>
                <?php else: ?>
                    <div style="background: #e0e0e0; color: #999; width: 50px; height: 50px; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        🚚
                    </div>
                    <p style="margin: 0; font-weight: bold; color: #999;">Preparando</p>
                <?php endif; ?>
                <p style="margin: 3px 0 0 0; font-size: 12px; color: #666;">Enviando...</p>
            </div>
            
            <!-- Línea conector -->
            <div style="flex: 1; height: 3px; background: <?php echo ($orden['estado_entrega'] === 'entregado') ? '#4caf50' : '#ddd'; ?>; margin: 0 10px 30px 10px;"></div>
            
            <!-- Paso 3: Entregado -->
            <div style="text-align: center; flex: 1;">
                <?php if($orden['estado_entrega'] === 'entregado'): ?>
                    <div style="background: #4caf50; color: white; width: 50px; height: 50px; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        ✓
                    </div>
                    <p style="margin: 0; font-weight: bold; color: #333;">Entregado</p>
                <?php else: ?>
                    <div style="background: #e0e0e0; color: #999; width: 50px; height: 50px; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        ✓
                    </div>
                    <p style="margin: 0; font-weight: bold; color: #999;">Próximamente</p>
                <?php endif; ?>
                <p style="margin: 3px 0 0 0; font-size: 12px; color: #666;">En tu puerta</p>
            </div>
        </div>
    </div>

    <!-- Información de la dirección con mapa -->
    <?php 
    // Calcular tiempo estimado de entrega
    $fecha_orden = strtotime($orden['fecha_orden']);
    $ahora = time();
    $dias_transcurridos = floor(($ahora - $fecha_orden) / 86400);
    
    // Estimación según estado
    if($orden['estado_entrega'] === 'entregado') {
        $tiempo_estimado = '✓ Entregado';
        $dias_restantes = 0;
    } else if($orden['estado_entrega'] === 'en_ruta') {
        $dias_restantes = max(1, 2 - $dias_transcurridos);
        $tiempo_estimado = '🚚 Llega en ' . $dias_restantes . ' día(s)';
    } else {
        $dias_restantes = max(1, 3 - $dias_transcurridos);
        $tiempo_estimado = '⏳ Estimado: ' . $dias_restantes . ' día(s)';
    }
    ?>
    
    <?php if($orden['latitud'] && $orden['longitud']): ?>
    <div style="margin-bottom: 20px;">
        <h4 style="color: #000000; margin-bottom: 15px;">📍 Tu Ubicación de Entrega</h4>
        
        <!-- Mapa -->
        <div id="mapa-entrega" style="width: 100%; height: 350px; border-radius: 8px; overflow: hidden; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        </div>
        
        <!-- Información de Tiempo y Dirección -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <!-- Tiempo Estimado -->
            <div style="background: #e8f4f8; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8; text-align: center;">
                <p style="margin: 0; font-size: 18px; font-weight: bold; color: #000000;">
                    <?php echo $tiempo_estimado; ?>
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                    Tiempo de entrega
                </p>
            </div>
            
            <!-- Estado -->
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; text-align: center;">
                <p style="margin: 0; font-size: 18px; font-weight: bold; color: #000000;">
                    <?php if($orden['estado_entrega'] === 'entregado'): ?>
                        ✓ Entregado
                    <?php elseif($orden['estado_entrega'] === 'en_ruta'): ?>
                        🚚 En Camino
                    <?php else: ?>
                        ⏳ Preparando
                    <?php endif; ?>
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                    Estado actual
                </p>
            </div>
        </div>
        
        <!-- Dirección -->
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8;">
            <p style="margin: 8px 0; color: #333;"><strong>📦 Dirección de Entrega:</strong></p>
            <p style="margin: 5px 0 0 0; color: #666;">
                <?php echo $orden['calle'] . ' ' . $orden['numero']; ?>
                <?php if($orden['apartamento']) echo ', Apto. ' . $orden['apartamento']; ?>
            </p>
            <p style="margin: 5px 0 0 0; color: #666;">
                <?php echo $orden['ciudad']; ?>
                <?php if($orden['estado']) echo ', ' . $orden['estado']; ?>
                <?php if($orden['codigo_postal']) echo ' ' . $orden['codigo_postal']; ?>
            </p>
            <?php if($orden['pais']): ?>
                <p style="margin: 5px 0 0 0; color: #666;"><?php echo $orden['pais']; ?></p>
            <?php endif; ?>
            <?php if($orden['referencia']): ?>
                <p style="margin: 10px 0 0 0; color: #888; font-size: 13px;"><strong>Referencia:</strong> <?php echo $orden['referencia']; ?></p>
            <?php endif; ?>
            <?php if($orden['latitud'] && $orden['longitud']): ?>
                <p style="margin: 10px 0 0 0; color: #17a2b8; font-size: 13px; background: #e8f4f8; padding: 8px; border-radius: 4px;"><strong>📍 GPS:</strong> <?php echo number_format($orden['latitud'], 6); ?>, <?php echo number_format($orden['longitud'], 6); ?></p>
            <?php else: ?>
                <p style="margin: 10px 0 0 0; color: #999; font-size: 13px;"><strong>📍 GPS:</strong> No disponible aún</p>
            <?php endif; ?>
        </div>
        
        <!-- Botón de Confirmación de Entrega -->
        <?php if($orden['estado_entrega'] === 'en_ruta'): ?>
        <div style="margin-top: 15px; text-align: center;">
            <button onclick="confirmarEntrega(<?php echo $orden['idorden']; ?>)" style="background: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: bold;">
                <i class="fas fa-thumbs-up"></i> ¡Muchas gracias! Ya llegó
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php elseif($orden['calle']): ?>
    <div style="margin-bottom: 20px;">
        <h4 style="color: #000000; margin-bottom: 15px;">📍 Tu Ubicación de Entrega</h4>
        
        <!-- Información de Tiempo y Estado -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <!-- Tiempo Estimado -->
            <div style="background: #e8f4f8; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8; text-align: center;">
                <p style="margin: 0; font-size: 18px; font-weight: bold; color: #000000;">
                    <?php echo $tiempo_estimado; ?>
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                    Tiempo de entrega
                </p>
            </div>
            
            <!-- Estado -->
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; text-align: center;">
                <p style="margin: 0; font-size: 18px; font-weight: bold; color: #000000;">
                    <?php if($orden['estado_entrega'] === 'entregado'): ?>
                        ✓ Entregado
                    <?php elseif($orden['estado_entrega'] === 'en_ruta'): ?>
                        🚚 En Camino
                    <?php else: ?>
                        ⏳ Preparando
                    <?php endif; ?>
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                    Estado actual
                </p>
            </div>
        </div>
        
        <!-- Dirección y GPS -->
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8;">
            <p style="margin: 8px 0; color: #333;"><strong>📦 Dirección de Entrega:</strong></p>
            <p style="margin: 5px 0 0 0; color: #666;">
                <?php echo $orden['calle'] . ' ' . $orden['numero']; ?>
                <?php if($orden['apartamento']) echo ', Apto. ' . $orden['apartamento']; ?>
            </p>
            <p style="margin: 5px 0 0 0; color: #666;">
                <?php echo $orden['ciudad']; ?>
                <?php if($orden['estado']) echo ', ' . $orden['estado']; ?>
                <?php if($orden['codigo_postal']) echo ' ' . $orden['codigo_postal']; ?>
            </p>
            <?php if($orden['pais']): ?>
                <p style="margin: 5px 0 0 0; color: #666;"><?php echo $orden['pais']; ?></p>
            <?php endif; ?>
            <?php if($orden['referencia']): ?>
                <p style="margin: 10px 0 0 0; color: #888; font-size: 13px;"><strong>Referencia:</strong> <?php echo $orden['referencia']; ?></p>
            <?php endif; ?>
            <?php if($orden['latitud'] && $orden['longitud']): ?>
                <p style="margin: 10px 0 0 0; color: #17a2b8; font-size: 13px;"><strong>📍 GPS:</strong> <?php echo number_format($orden['latitud'], 6); ?>, <?php echo number_format($orden['longitud'], 6); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
        <div style="margin-bottom: 20px;">
            <h4 style="color: #000000; margin-bottom: 15px;">📍 Ubicación de Entrega</h4>
            
            <!-- Información de Tiempo y Estado -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <!-- Tiempo Estimado -->
                <div style="background: #e8f4f8; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8; text-align: center;">
                    <p style="margin: 0; font-size: 18px; font-weight: bold; color: #000000;">
                        <?php echo $tiempo_estimado; ?>
                    </p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                        Tiempo de entrega
                    </p>
                </div>
                
                <!-- Estado -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; text-align: center;">
                    <p style="margin: 0; font-size: 18px; font-weight: bold; color: #000000;">
                        <?php if($orden['estado_entrega'] === 'entregado'): ?>
                            ✓ Entregado
                        <?php elseif($orden['estado_entrega'] === 'en_ruta'): ?>
                            🚚 En Camino
                        <?php else: ?>
                            ⏳ Preparando
                        <?php endif; ?>
                    </p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                        Estado actual
                    </p>
                </div>
            </div>
            
            <!-- Dirección -->
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
                <p style="color: #856404; margin: 0;"><i class="fas fa-info-circle"></i> <strong>ℹ️ Información de Entrega</strong></p>
                <p style="color: #856404; margin: 10px 0 0 0; font-size: 14px;">No hay dirección de entrega registrada en este pedido. Si realizaste el pago, la ubicación se mostrará aquí una vez que nuestro equipo prepare tu envío.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Detalles de la compra -->
    <div style="margin-top: 20px;">
        <h4 style="color: #000000; margin-bottom: 15px;">📦 Productos</h4>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                    <th style="padding: 10px; text-align: left; color: #333;">Producto</th>
                    <th style="padding: 10px; text-align: center; color: #333;">Cantidad</th>
                    <th style="padding: 10px; text-align: right; color: #333;">Precio</th>
                    <th style="padding: 10px; text-align: right; color: #333;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($detalle = $resultado_detalles->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; color: #333;"><?php echo $detalle['nombre_producto']; ?></td>
                    <td style="padding: 12px; text-align: center; color: #333;"><?php echo $detalle['cantidad']; ?></td>
                    <td style="padding: 12px; text-align: right; color: #333;">$<?php echo number_format($detalle['precio'], 2); ?></td>
                    <td style="padding: 12px; text-align: right; color: #333;">$<?php echo number_format($detalle['subtotal'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Total -->
    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; text-align: right;">
        <p style="margin: 0; color: #666;">
            <strong style="font-size: 18px; color: #000000;">Total: $<?php echo number_format($orden['total'], 2); ?></strong>
        </p>
    </div>

    <!-- Método de pago -->
    <div style="margin-top: 20px; padding: 15px; background: #e8f4f8; border-radius: 8px; border-left: 4px solid #17a2b8;">
        <p style="margin: 0; color: #333;"><strong>Método de Pago:</strong> <?php echo ucfirst($orden['metodo_pago']); ?></p>
        <?php if($orden['tienda_cercana']): ?>
            <p style="margin: 5px 0 0 0; color: #333;"><strong>Tienda:</strong> <?php echo $orden['tienda_cercana']; ?></p>
        <?php endif; ?>
        <?php if($orden['codigo_barras']): ?>
            <p style="margin: 5px 0 0 0; color: #333;"><strong>Código de Barras:</strong> <?php echo $orden['codigo_barras']; ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Leaflet Maps CSS y JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
<?php if($orden['latitud'] && $orden['longitud']): ?>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar mapa
        const lat = <?php echo $orden['latitud']; ?>;
        const lng = <?php echo $orden['longitud']; ?>;
        
        const mapa = L.map('mapa-entrega').setView([lat, lng], 15);
        
        // Agregar capa de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(mapa);
        
        // Color del marcador según estado de entrega
        const estadoEntrega = '<?php echo $orden['estado_entrega']; ?>';
        let colorMarcador = '#0078ff'; // Azul por defecto
        
        if(estadoEntrega === 'en_ruta') {
            colorMarcador = '#ff0000'; // Rojo cuando está en ruta
        } else if(estadoEntrega === 'entregado') {
            colorMarcador = '#28a745'; // Verde cuando está entregado
        }
        
        // Crear marcador personalizado
        const marcador = L.circleMarker([lat, lng], {
            radius: 12,
            fillColor: colorMarcador,
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(mapa);
        
        marcador.bindPopup('<strong><?php echo htmlspecialchars($orden['ciudad']); ?></strong><br><?php echo htmlspecialchars($orden['calle'] . ' ' . $orden['numero']); ?>')
            .openPopup();
        
        // Ajustar mapa al cargar
        mapa.invalidateSize();
    });

    function confirmarEntrega(idorden) {
        if(!confirm('¿Confirmar que tu pedido ya ha llegado a tu destino?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('idorden', idorden);
        formData.append('accion', 'entregado');
        
        fetch('actualizar_entrega.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload(); // Recargar para mostrar cambios
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al confirmar entrega');
        });
    }
<?php endif; ?>
</script>

<?php
$stmt_detalles->close();
$conexion->close();
?>

