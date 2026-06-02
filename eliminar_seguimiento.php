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
$sql_check = "SELECT idorden, estado_entrega FROM ordenes WHERE idorden = ? AND idusuario = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("ii", $idorden, $usuario_id);
$stmt_check->execute();
$resultado = $stmt_check->get_result();

if($resultado->num_rows === 0) {
    die("Orden no encontrada");
}
$orden = $resultado->fetch_assoc();
$stmt_check->close();

// Eliminar orden si está entregada
if($accion === 'eliminar') {
    if($orden['estado_entrega'] !== 'entregado') {
        echo json_encode(['success' => false, 'message' => '✗ Solo puedes eliminar órdenes entregadas']);
        exit();
    }
    
    // Eliminar detalles de la orden
    $sql_detalles = "DELETE FROM detalles_orden WHERE idorden = ?";
    $stmt_detalles = $conexion->prepare($sql_detalles);
    $stmt_detalles->bind_param("i", $idorden);
    $stmt_detalles->execute();
    $stmt_detalles->close();
    
    // Eliminar la orden
    $sql_delete = "DELETE FROM ordenes WHERE idorden = ? AND idusuario = ?";
    $stmt_delete = $conexion->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $idorden, $usuario_id);
    
    if($stmt_delete->execute()) {
        echo json_encode(['success' => true, 'message' => '✓ Seguimiento eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => '✗ Error al eliminar']);
    }
    $stmt_delete->close();
}

$conexion->close();
?>
