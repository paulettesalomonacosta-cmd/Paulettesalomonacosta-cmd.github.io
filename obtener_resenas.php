<?php
require 'conexion.php';

header('Content-Type: application/json');

$idproductos = intval($_GET['idproductos'] ?? 0);

if ($idproductos <= 0) {
    echo json_encode(['success' => false, 'message' => 'Producto inválido']);
    exit;
}

// Obtener reseñas del producto
$sql = "SELECT r.idresena, r.calificacion, r.comentario, r.fecha_creacion, u.nombre
        FROM resenas r
        LEFT JOIN usuarios u ON r.idusuarios = u.idusuarios
        WHERE r.idproductos = ? AND r.estado = 'aprobada'
        ORDER BY r.fecha_creacion DESC
        LIMIT 10";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en consulta']);
    exit;
}

$stmt->bind_param('i', $idproductos);
$stmt->execute();
$resultado = $stmt->get_result();

$resenas = [];
while ($fila = $resultado->fetch_assoc()) {
    // Generar estrellas
    $estrellas = '';
    for ($i = 1; $i <= 5; $i++) {
        $estrellas .= $i <= $fila['calificacion'] ? '★' : '☆';
    }
    
    $resenas[] = [
        'usuario' => $fila['nombre'] ?? 'Anónimo',
        'estrellas' => $estrellas,
        'calificacion' => $fila['calificacion'],
        'comentario' => $fila['comentario'],
        'fecha' => date('d/m/Y', strtotime($fila['fecha_creacion']))
    ];
}

echo json_encode([
    'success' => true,
    'resenas' => $resenas,
    'total' => count($resenas)
]);

$stmt->close();
$conexion->close();
?>
