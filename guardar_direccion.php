<?php
session_start();
include("conexion.php");

header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id'])) {
    echo json_encode(['éxito' => false, 'mensaje' => 'Debes iniciar sesión']);
    exit();
}

$datos = json_decode(file_get_contents('php://input'), true);
$idusuarios = $_SESSION['usuario_id'];

$calle = $conexion->real_escape_string($datos['calle']);
$numero = $conexion->real_escape_string($datos['numero']);
$apartamento = $conexion->real_escape_string($datos['apartamento']);
$ciudad = $conexion->real_escape_string($datos['ciudad']);
$estado = $conexion->real_escape_string($datos['estado']);
$codigo_postal = $conexion->real_escape_string($datos['codigo_postal']);
$pais = $conexion->real_escape_string($datos['pais']);
$referencia = $conexion->real_escape_string($datos['referencia']);
$latitud = $datos['latitud'];
$longitud = $datos['longitud'];

// Si es la primera dirección, hacerla predeterminada
$sql_check = "SELECT COUNT(*) as count FROM direcciones_entrega WHERE idusuarios = $idusuarios";
$resultado = $conexion->query($sql_check);
$fila = $resultado->fetch_assoc();
$es_predeterminada = ($fila['count'] == 0) ? 1 : 0;

$sql = "INSERT INTO direcciones_entrega 
        (idusuarios, calle, numero, apartamento, ciudad, estado, codigo_postal, pais, latitud, longitud, referencia, es_predeterminada)
        VALUES 
        ($idusuarios, '$calle', '$numero', '$apartamento', '$ciudad', '$estado', '$codigo_postal', '$pais', $latitud, $longitud, '$referencia', $es_predeterminada)";

if($conexion->query($sql)) {
    $iddireccion = $conexion->insert_id;
    echo json_encode(['éxito' => true, 'mensaje' => 'Dirección guardada', 'iddireccion' => $iddireccion]);
} else {
    echo json_encode(['éxito' => false, 'mensaje' => 'Error: ' . $conexion->error]);
}
?>
