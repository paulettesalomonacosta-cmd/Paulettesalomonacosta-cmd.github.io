<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['usuario_id'])) {
    die("No autorizado");
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido");
}

$usuario_id = $_SESSION['usuario_id'];
$idorden = isset($_POST['idorden']) ? intval($_POST['idorden']) : 0;
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if(!$idorden || !$accion) {
    die("Parámetros faltantes");
}

// Verificar que la orden pertenezca al usuario
$sql_check = "SELECT idorden FROM ordenes WHERE idorden = ? AND idusuario = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("ii", $idorden, $usuario_id);
$stmt_check->execute();
$resultado = $stmt_check->get_result();

if($resultado->num_rows === 0) {
    die("Orden no encontrada");
}
$stmt_check->close();

// Actualizar estado de entrega
if($accion === 'entregado') {
    $nuevo_estado = 'entregado';
    $sql_update = "UPDATE ordenes SET estado_entrega = ? WHERE idorden = ? AND idusuario = ?";
    $stmt = $conexion->prepare($sql_update);
    $stmt->bind_param("sii", $nuevo_estado, $idorden, $usuario_id);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '✓ ¡Gracias! Tu pedido ha sido marcado como entregado.']);
    } else {
        echo json_encode(['success' => false, 'message' => '✗ Error al actualizar']);
    }
    $stmt->close();
} elseif($accion === 'en_ruta') {
    $nuevo_estado = 'en_ruta';
    $sql_update = "UPDATE ordenes SET estado_entrega = ? WHERE idorden = ? AND idusuario = ?";
    $stmt = $conexion->prepare($sql_update);
    $stmt->bind_param("sii", $nuevo_estado, $idorden, $usuario_id);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '✓ Pedido marcado como en ruta']);
    } else {
        echo json_encode(['success' => false, 'message' => '✗ Error al actualizar']);
    }
    $stmt->close();
}

$conexion->close();
?>
