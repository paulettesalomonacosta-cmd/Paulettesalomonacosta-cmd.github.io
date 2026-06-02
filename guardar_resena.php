<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar si el usuario está logeado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes estar logeado para dejar una reseña']);
    exit;
}

$idproductos = intval($_POST['idproductos'] ?? 0);
$calificacion = intval($_POST['calificacion'] ?? 5);
$comentario = trim($_POST['comentario'] ?? '');
$idusuarios = $_SESSION['usuario_id'];

// Validaciones
if ($idproductos <= 0) {
    echo json_encode(['success' => false, 'message' => 'Producto inválido']);
    exit;
}

if ($calificacion < 1 || $calificacion > 5) {
    $calificacion = 5;
}

if (strlen($comentario) > 500) {
    $comentario = substr($comentario, 0, 500);
}

// Insertar reseña
$sql = "INSERT INTO resenas (idproductos, idusuarios, calificacion, comentario) 
        VALUES (?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en consulta: ' . $conexion->error]);
    exit;
}

$stmt->bind_param('iis', $idproductos, $idusuarios, $calificacion, $comentario);

if ($stmt->execute()) {
    // Actualizar calificación promedio del producto
    $sql_update = "UPDATE productos SET calificacion = (
        SELECT AVG(calificacion) FROM resenas 
        WHERE idproductos = ? AND estado = 'aprobada'
    ) WHERE idproductos = ?";
    
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param('ii', $idproductos, $idproductos);
    $stmt_update->execute();
    
    echo json_encode(['success' => true, 'message' => 'Reseña guardada exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar reseña: ' . $stmt->error]);
}

$stmt->close();
$conexion->close();
?>
