<?php

$conexion = new mysqli("localhost","root","","tiendaonline");

if($conexion->connect_error){
    die("Error de conexión");
}

?>
