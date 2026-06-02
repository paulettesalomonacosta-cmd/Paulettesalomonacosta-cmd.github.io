<?php
session_start();
include("conexion.php");

if(empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit();
}

// Obtener dirección de entrega
$iddireccion = isset($_GET['iddireccion']) ? intval($_GET['iddireccion']) : 0;
$direccion_entrega = null;

if($iddireccion) {
    $sql_dir = "SELECT * FROM direcciones_entrega WHERE iddireccion = $iddireccion AND idusuarios = " . ($_SESSION['usuario_id'] ?? 0);
    $resultado_dir = $conexion->query($sql_dir);
    if($resultado_dir && $resultado_dir->num_rows > 0) {
        $direccion_entrega = $resultado_dir->fetch_assoc();
    }
}

if(!$direccion_entrega) {
    header("Location: seleccionar_direccion.php");
    exit();
}

// Calcular total del carrito
$total = 0;
foreach($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

$descuento_juego = isset($_SESSION['juego_descuento']) ? intval($_SESSION['juego_descuento']) : 0;
$descuento_total = round($total * $descuento_juego / 100, 2);
$total_con_descuento = $total - $descuento_total;
$compra_requerida = !empty($_SESSION['compra_requerida']);

// Verificar si el usuario está logueado
$usuario_logueado = isset($_SESSION['usuario_id']) ? true : false;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Métodos de Pago - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .pago-container {
        max-width: 900px;
        margin: 30px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .pago-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .pago-header h2 {
        color: #000000;
        font-size: 28px;
        margin-bottom: 10px;
    }

    .resumen-compra {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        border-left: 4px solid #000000;
    }

    .resumen-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
    }

    .resumen-item:last-child {
        border-bottom: none;
    }

    .resumen-total {
        font-size: 20px;
        font-weight: bold;
        color: #000000;
        padding: 15px 0;
        text-align: right;
    }

    .metodos-pago {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .metodo-card {
        border: 2px solid #ddd;
        border-radius: 10px;
        padding: 25px;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }

    .metodo-card:hover {
        border-color: #000000;
        box-shadow: 0 4px 15px rgba(0,43,91,0.2);
    }

    .metodo-card.active {
        border-color: #28a745;
        background: #f0f8f0;
    }

    .metodo-card input[type="radio"] {
        display: none;
    }

    .metodo-icon {
        font-size: 40px;
        margin-bottom: 15px;
        color: #000000;
    }

    .metodo-card.active .metodo-icon {
        color: #28a745;
    }

    .metodo-titulo {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #000000;
    }

    .metodo-descripcion {
        font-size: 14px;
        color: #666;
        line-height: 1.5;
    }

    .formulario-pago {
        display: none;
        background: #f8f9fa;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .formulario-pago.active {
        display: block;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #000000;
        font-weight: bold;
        font-size: 14px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #000000;
        box-shadow: 0 0 5px rgba(0,43,91,0.2);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .botones-pago {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn-cancelar, .btn-procesar {
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-cancelar {
        background: #666;
        color: white;
    }

    .btn-cancelar:hover {
        background: #555;
    }

    .btn-procesar {
        background: #28a745;
        color: white;
    }

    .btn-procesar:hover {
        background: #218838;
    }

    .alerta {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        border-left: 4px solid;
    }

    .alerta.error {
        background: #f8d7da;
        border-color: #dc3545;
        color: #721c24;
    }

    .alerta.info {
        background: #d1ecf1;
        border-color: #17a2b8;
        color: #0c5460;
    }

    @media (max-width: 600px) {
        .metodos-pago {
            grid-template-columns: 1fr;
        }
        .form-row {
            grid-template-columns: 1fr;
        }
    }


</style>
</head>
<body>

<div class="pago-container">
    <a href="carrito.php" style="display: inline-block; margin-bottom: 20px; color: #000000; text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Volver al carrito
    </a>

    <div class="pago-header">
        <h2><i class="fas fa-credit-card"></i> Selecciona tu Método de Pago</h2>
    </div>

    <!-- Resumen de Compra -->
    <div class="resumen-compra">
        <h3 style="color: #000000; margin-top: 0;">Resumen de tu Compra</h3>
        <?php 
        $cantidad_items = 0;
        foreach($_SESSION['carrito'] as $item): 
            $cantidad_items += $item['cantidad'];
        ?>
            <div class="resumen-item">
                <span><?php echo $item['nombre']; ?> (x<?php echo $item['cantidad']; ?>)</span>
                <span>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></span>
            </div>
        <?php endforeach; ?>
        <?php if ($descuento_juego > 0): ?>
            <div class="resumen-item">
                <span>Descuento MaxArena</span>
                <span>- $<?php echo number_format($descuento_total,2); ?></span>
            </div>
            <div class="resumen-total" style="color:#1b5e20; font-weight:800;">
                Total con descuento: $<?php echo number_format($total_con_descuento,2); ?>
            </div>
        <?php else: ?>
            <div class="resumen-total">
                Total: $<?php echo number_format($total, 2); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dirección de Entrega -->
    <div class="resumen-compra" style="border-left-color: #28a745;">
        <h3 style="color: #28a745; margin-top: 0;">Dirección de Entrega</h3>
        <p><strong><?php echo htmlspecialchars($direccion_entrega['calle'] . ' ' . $direccion_entrega['numero']); ?></strong></p>
        <p><?php echo htmlspecialchars(($direccion_entrega['apartamento'] ? 'Apt. ' . $direccion_entrega['apartamento'] . ' - ' : '') . $direccion_entrega['ciudad'] . ', ' . $direccion_entrega['estado']); ?></p>
        <p><?php echo htmlspecialchars($direccion_entrega['codigo_postal'] . ' ' . $direccion_entrega['pais']); ?></p>
        <?php if(!empty($direccion_entrega['referencia'])): ?>
            <p><em><?php echo htmlspecialchars($direccion_entrega['referencia']); ?></em></p>
        <?php endif; ?>
    </div>

    <!-- Selección de Método de Pago -->
    <form id="formularioPago" method="POST" action="procesar_pago.php">
        <input type="hidden" name="iddireccion" value="<?php echo $iddireccion; ?>">
        <input type="hidden" name="descuento_aplicado" value="<?php echo $descuento_juego; ?>">
        <?php if ($compra_requerida): ?>
            <div class="alerta warning" style="margin-bottom: 18px; background:#fff4e5; border-color:#ffb236; color:#7a4a04;">
                <i class="fas fa-exclamation-circle"></i> 🎮 Ya jugaste MaxArena. Esta compra DEBE ser mínimo de $100 para poder jugar de nuevo.
            </div>
        <?php endif; ?>
        <div class="metodos-pago">
            <!-- Pago con Tarjeta -->
            <div class="metodo-card" onclick="seleccionarMetodo(this, 'tarjeta')">
                <input type="radio" name="metodo_pago" value="tarjeta" required>
                <div class="metodo-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="metodo-titulo">Pago con Tarjeta</div>
                <div class="metodo-descripcion">
                    Débito o Crédito
                </div>
            </div>

            <!-- Pagar en Tienda Cercana -->
            <div class="metodo-card" onclick="seleccionarMetodo(this, 'tienda')">
                <input type="radio" name="metodo_pago" value="tienda" required>
                <div class="metodo-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="metodo-titulo">Pagar en Tienda</div>
                <div class="metodo-descripcion">
                    Genera tu código de barras y paga en sucursal cercana
                </div>
            </div>
        </div>

        <!-- Formulario Tarjeta -->
        <div id="formulario-tarjeta" class="formulario-pago">
            <h3 style="color: #000000; margin-top: 0;">Datos de la Tarjeta</h3>
            
            <div class="alerta info">
                <i class="fas fa-shield-alt"></i> Tu información está protegida y encriptada
            </div>

            <div class="form-group">
                <label for="nombre_tarjeta">Nombre en la Tarjeta *</label>
                <input type="text" id="nombre_tarjeta" name="nombre_tarjeta" placeholder="Juan García" maxlength="100" data-tarjeta="true">
            </div>

            <div class="form-group">
                <label for="numero_tarjeta">Número de Tarjeta *</label>
                <input type="text" id="numero_tarjeta" name="numero_tarjeta" placeholder="1234 5678 9012 3456" maxlength="20" inputmode="numeric" data-tarjeta="true">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_vencimiento">Fecha de Vencimiento (MM/AA) *</label>
                    <input type="text" id="fecha_vencimiento" name="fecha_vencimiento" placeholder="12/25" maxlength="5" inputmode="numeric" data-tarjeta="true">
                </div>
                <div class="form-group">
                    <label for="cvv">CVV *</label>
                    <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4" inputmode="numeric" data-tarjeta="true">
                </div>
            </div>
        </div>

        <!-- Formulario Tienda -->
        <div id="formulario-tienda" class="formulario-pago">
            <h3 style="color: #000000; margin-top: 0;">Pago en Tienda Cercana</h3>
            
            <div class="alerta info">
                <i class="fas fa-barcode"></i> Se generará un código de barras que deberás presentar en la tienda
            </div>

            <div class="form-group">
                <label for="tienda_cercana">Selecciona tu Tienda Cercana *</label>
                <select id="tienda_cercana" name="tienda_cercana" data-tienda="true">
                    <option value="">-- Selecciona una tienda --</option>
                    <option value="Tienda Centro">Tienda Centro - Av. Principal 123</option>
                    <option value="Tienda Norte">Tienda Norte - Calle 5 #456</option>
                    <option value="Tienda Sur">Tienda Sur - Carrera 10 #789</option>
                    <option value="Tienda Este">Tienda Este - Diagonal 20 #321</option>
                    <option value="Tienda Oeste">Tienda Oeste - Calle 8 #654</option>
                </select>
            </div>

            <div class="alerta">
                <strong>Instrucciones:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <li>Se generará tu código de barras después de confirmar</li>
                    <li>Debes presentar el código en la tienda seleccionada</li>
                    <li>El plazo para pagar es de 3 días hábiles</li>
                    <li>Tu pedido será confirmado una vez realizado el pago</li>
                </ul>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="botones-pago">
            <a href="carrito.php">
                <button type="button" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </a>
            <button type="submit" class="btn-procesar">
                <i class="fas fa-check"></i> Procesar Compra
            </button>
        </div>
    </form>
</div>

<script>
    // Variable PHP para verificar si usuario está logueado
    const usuarioLogueado = <?php echo json_encode($usuario_logueado); ?>;

    // Inicializar con tarjeta seleccionada
    document.addEventListener('DOMContentLoaded', function() {
        const primerMetodo = document.querySelector('.metodo-card');
        if(primerMetodo) {
            primerMetodo.click();
        }
    });
    
    function seleccionarMetodo(elemento, tipo) {
        // Remover clase active de todos los cards
        document.querySelectorAll('.metodo-card').forEach(card => {
            card.classList.remove('active');
        });
        
        // Agregar clase active al card seleccionado
        elemento.classList.add('active');
        
        // Marcar el radio button
        elemento.querySelector('input[type="radio"]').checked = true;
        
        // Mostrar/Ocultar formularios
        document.getElementById('formulario-tarjeta').classList.remove('active');
        document.getElementById('formulario-tienda').classList.remove('active');
        
        if(tipo === 'tarjeta') {
            document.getElementById('formulario-tarjeta').classList.add('active');
        } else {
            document.getElementById('formulario-tienda').classList.add('active');
        }
    }

    // Validar formulario antes de enviar
    const formularioPago = document.getElementById('formularioPago');
    let envioEnProgreso = false;

    document.getElementById('formularioPago').addEventListener('submit', function(e) {
        if(envioEnProgreso) {
            return;
        }

        const metodoPagoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
        
        if(!metodoPagoSeleccionado) {
            e.preventDefault();
            alert('Por favor selecciona un método de pago');
            return false;
        }
        
        const metodoPago = metodoPagoSeleccionado.value;
        
        if(metodoPago === 'tarjeta') {
            // Verificar si el usuario está logueado
            if(!usuarioLogueado) {
                e.preventDefault();
                window.location.href = 'login.php?error=Debes iniciar sesión para completar la compra';
                return false;
            }

            const numTarjeta = document.getElementById('numero_tarjeta').value.replace(/\s/g, '');
            const fechaVencimiento = document.getElementById('fecha_vencimiento').value.trim();
            const cvv = document.getElementById('cvv').value.trim();
            const nombreTarjeta = document.getElementById('nombre_tarjeta').value.trim();
            
            if(!nombreTarjeta) {
                e.preventDefault();
                alert('Por favor ingresa el nombre del titular de la tarjeta');
                return false;
            }
            
            if(!numTarjeta) {
                e.preventDefault();
                alert('Por favor ingresa el número de tarjeta');
                return false;
            }
            
            if(numTarjeta.length < 13 || numTarjeta.length > 19) {
                e.preventDefault();
                alert('El número de tarjeta debe tener entre 13 y 19 dígitos');
                return false;
            }
            
            if(!fechaVencimiento || fechaVencimiento.length < 5) {
                e.preventDefault();
                alert('Por favor ingresa la fecha de vencimiento en formato MM/AA');
                return false;
            }
            
            if(!cvv) {
                e.preventDefault();
                alert('Por favor ingresa el CVV');
                return false;
            }
            
            if(cvv.length < 3 || cvv.length > 4 || isNaN(cvv)) {
                e.preventDefault();
                alert('CVV inválido (debe ser 3 o 4 números)');
                return false;
            }

            e.preventDefault();
            envioEnProgreso = true;
            setTimeout(() => formularioPago.submit(), 500);
            return false;
        }
        
        if(metodoPago === 'tienda') {
            // Verificar si el usuario está logueado
            if(!usuarioLogueado) {
                e.preventDefault();
                window.location.href = 'login.php?error=Debes iniciar sesión para completar la compra';
                return false;
            }

            const tienda = document.getElementById('tienda_cercana').value;
            if(!tienda) {
                e.preventDefault();
                alert('Por favor selecciona una tienda');
                return false;
            }
            
            // Si llegamos aquí, permito el envío natural del formulario para pago en tienda
            // (no necesita mostrar el robot de confeti)
        }
    });

    // Formato automático para número de tarjeta
    document.getElementById('numero_tarjeta').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '');
        let formattedValue = value.replace(/(\d{4})/g, '$1 ').trim();
        e.target.value = formattedValue;
    });

    // Formato automático para fecha de vencimiento
    document.getElementById('fecha_vencimiento').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if(value.length >= 2) {
            value = value.slice(0, 2) + '/' + value.slice(2, 4);
        }
        e.target.value = value;
    });
</script>

</body>
</html>
