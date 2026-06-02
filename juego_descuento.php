<?php
session_start();
header('Content-Type: application/json');
include('conexion.php');

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : null;

if ($method === 'GET' && $action === 'estado') {
    $canPlay = false;
    $mensaje_bloqueado = null;
    $juego_descuento = isset($_SESSION['juego_descuento']) ? intval($_SESSION['juego_descuento']) : 0;
    $intento_disponible = isset($_SESSION['intento_disponible']) ? intval($_SESSION['intento_disponible']) : 0;
    
    // Verificar si ya tiene compra pagada confirmada
    $compra_pagada = false;
    if(isset($_SESSION['usuario_id'])) {
        // Verificar en la BD si tiene ordenes pagadas >= $100
        $sql_check = "SELECT COUNT(*) as total_pagadas FROM ordenes WHERE idusuario = ? AND estado = 'pagado' AND total >= 100";
        $stmt_check = $conexion->prepare($sql_check);
        if($stmt_check) {
            $usuario_id = $_SESSION['usuario_id'];
            $stmt_check->bind_param("i", $usuario_id);
            $stmt_check->execute();
            $resultado_check = $stmt_check->get_result();
            $row = $resultado_check->fetch_assoc();
            if($row['total_pagadas'] > 0) {
                $compra_pagada = true;
            }
            $stmt_check->close();
        }
    }
    
    // REGLA 1: Si ya ganó, debe pagar de nuevo
    if (!empty($_SESSION['compra_requerida'])) {
        $canPlay = false;
        $mensaje_bloqueado = '💳 YA JUGASTE: Necesitas pagar $100+ MÁS para obtener otro intento.';
    }
    // REGLA 2: Si no tiene intento disponible
    else if ($intento_disponible <= 0) {
        $canPlay = false;
        $mensaje_bloqueado = '❌ SIN INTENTOS: Realiza una compra de $100+ para obtener un intento.';
    }
    // REGLA 3: Si aún no ha pagado nada
    else if (!$compra_pagada) {
        $canPlay = false;
        $mensaje_bloqueado = '💳 COMPRA REQUERIDA: Paga $100+ para obtener tu primer intento.';
    }
    // REGLA 4: Si todo está bien, puede jugar
    else {
        $canPlay = true;
    }
    
    $resp = [
        'success' => true,
        'canPlay' => $canPlay,
        'juego_descuento' => $juego_descuento,
        'intento_disponible' => $intento_disponible,
        'compra_requerida' => !empty($_SESSION['compra_requerida']),
        'compra_pagada' => $compra_pagada,
        'mensaje_bloqueado' => $mensaje_bloqueado
    ];
    echo json_encode($resp);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Sin datos']);
    exit();
}

if (!isset($input['action']) || $input['action'] !== 'resultadoJuego') {
    echo json_encode(['success' => false, 'message' => 'Acción inválida']);
    exit();
}

$resultado = $input['resultado'] ?? '';

// VALIDACIÓN CRÍTICA: Verificar que pueda jugar
// 1. Si ya ganó y debe pagar de nuevo, rechazar
if (!empty($_SESSION['compra_requerida'])) {
    echo json_encode([
        'success' => false,
        'message' => '❌ ACCESO DENEGADO: Ya usaste tu intento.\n💳 Paga $100+ MÁS para obtener otro intento.'
    ]);
    exit();
}

// 2. Verificar que tenga intento disponible
$intento_disponible = isset($_SESSION['intento_disponible']) ? intval($_SESSION['intento_disponible']) : 0;
if ($intento_disponible <= 0) {
    echo json_encode([
        'success' => false,
        'message' => '❌ SIN INTENTOS: Realiza una compra de $100+ para obtener un intento.'
    ]);
    exit();
}

// 3. Verificar que tenga compra pagada confirmada en BD
$compra_pagada = false;
if(isset($_SESSION['usuario_id'])) {
    $sql_check = "SELECT COUNT(*) as total_pagadas FROM ordenes WHERE idusuario = ? AND estado = 'pagado' AND total >= 100";
    $stmt_check = $conexion->prepare($sql_check);
    if($stmt_check) {
        $usuario_id = $_SESSION['usuario_id'];
        $stmt_check->bind_param("i", $usuario_id);
        $stmt_check->execute();
        $resultado_check = $stmt_check->get_result();
        $row = $resultado_check->fetch_assoc();
        if($row['total_pagadas'] > 0) {
            $compra_pagada = true;
        }
        $stmt_check->close();
    }
}

if (!$compra_pagada) {
    echo json_encode([
        'success' => false,
        'message' => '❌ COMPRA NO CONFIRMADA: Debes completar una compra de $100+ primero.\n💳 Ve al carrito y realiza el pago.'
    ]);
    exit();
}

// PROCESAR RESULTADO - Sistema de 1 intento por pago
if ($resultado === 'win') {
    $descuento = rand(10,25);
    $_SESSION['juego_descuento'] = $descuento;
    $_SESSION['juego_ganado'] = true;
    $_SESSION['intento_disponible'] = 0;  // Agota el intento
    $_SESSION['compra_requerida'] = true; // Bloquea hasta nueva compra
    $message = "🎉 ¡GANASTE $descuento% DE DESCUENTO! 🏆\n💳 Ya usaste tu intento. Paga $100+ MÁS para jugar de nuevo.";
    echo json_encode(['success' => true, 'ganado' => true, 'descuento' => $descuento, 'message' => $message]);
    exit();
}

if ($resultado === 'loss') {
    unset($_SESSION['juego_descuento']);
    $_SESSION['juego_ganado'] = false;
    $_SESSION['intento_disponible'] = 0;  // Agota el intento
    $_SESSION['compra_requerida'] = true; // Bloquea hasta nueva compra
    $message = '😞 Perdiste tu intento.\n💳 Paga $100+ MÁS para obtener otro intento y jugar de nuevo.';
    echo json_encode(['success' => true, 'ganado' => false, 'descuento' => 0, 'message' => $message]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Resultado inválido']);
exit();
