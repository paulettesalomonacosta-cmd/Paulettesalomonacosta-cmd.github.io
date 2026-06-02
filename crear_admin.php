<?php
session_start();
include("conexion.php");

// Verificar si ya existe un usuario administrador
$sql_check = "SELECT * FROM usuarios WHERE nombre = 'Administrador'";
$resultado_check = $conexion->query($sql_check);

if($resultado_check && $resultado_check->num_rows > 0) {
    echo "<h2 style='color: orange;'>⚠️ Ya existe una cuenta administrador</h2>";
    $usuario = $resultado_check->fetch_assoc();
    echo "Datos actuales:<br>";
    echo "Nombre: " . $usuario['nombre'] . "<br>";
    echo "Email: " . $usuario['correo'] . "<br><br>";
    echo "<a href='login.php'>Ir a Login</a>";
} else {
    // Datos del administrador
    $nombre = "Administrador";
    $correo = "admin@maximaonline.store";
    $contraseña = "AdminMax2024";
    
    // Hash de la contraseña
    $contraseña_hash = password_hash($contraseña, PASSWORD_BCRYPT);
    
    // Insertar el nuevo usuario
    $sql = "INSERT INTO usuarios (nombre, correo, contraseña) VALUES ('$nombre', '$correo', '$contraseña_hash')";
    
    if($conexion->query($sql) === TRUE) {
        echo "<h2 style='color: green;'>✅ Cuenta Administrador Creada</h2>";
        echo "<p><strong>📧 Correo:</strong> <code>" . $correo . "</code></p>";
        echo "<p><strong>🔐 Contraseña:</strong> <code>" . $contraseña . "</code></p>";
        echo "<p><strong>⚠️ Guarda estas credenciales en un lugar seguro.</strong></p>";
        echo "<p><a href='login.php' style='color: #ff5500; font-weight: bold;'>→ Ir a Login</a></p>";
    } else {
        echo "<h2 style='color: red;'>❌ Error al crear la cuenta: " . $conexion->error . "</h2>";
    }
}

$conexion->close();
?>
