<?php
include("conexion.php");

$respuesta = [
    'tabla_ordenes' => false,
    'tabla_detalles' => false,
    'mensaje' => ''
];

// Verificar tabla ordenes
$resultado_ordenes = $conexion->query("SHOW TABLES LIKE 'ordenes'");
if($resultado_ordenes->num_rows > 0) {
    $respuesta['tabla_ordenes'] = true;
}

// Verificar tabla detalles_orden
$resultado_detalles = $conexion->query("SHOW TABLES LIKE 'detalles_orden'");
if($resultado_detalles->num_rows > 0) {
    $respuesta['tabla_detalles'] = true;
}

// Generar mensaje
if($respuesta['tabla_ordenes'] && $respuesta['tabla_detalles']) {
    $respuesta['mensaje'] = 'Sistema configurado correctamente. ¡Listo para usar!';
} else {
    $respuesta['mensaje'] = 'Faltan tablas. Ejecuta crear_tabla_ordenes.php';
}

header('Content-Type: application/json');
echo json_encode($respuesta);

$conexion->close();
?>
