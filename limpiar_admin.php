<?php
session_start();
include("conexion.php");

// Verificación de seguridad simple
if(!isset($_POST['confirmado']) || $_POST['confirmado'] !== 'si') {
    echo "<h2 style='color: #ff5500;'>⚠️ Limpieza de Administradores</h2>";
    echo "<p style='font-size: 16px; margin: 20px 0;'>Este script eliminará TODOS los usuarios excepto admin@maximaonline.store</p>";
    echo "<form method='POST' style='margin: 30px 0;'>";
    echo "<p><strong>Escribe 'SI CONFIRMO' para continuar:</strong></p>";
    echo "<input type='text' name='confirmacion' required style='padding: 10px; font-size: 16px; width: 300px;'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #ff5500; color: white; border: none; border-radius: 5px; margin-left: 10px; font-size: 16px;'>Continuar</button>";
    echo "</form>";
    
    if(isset($_POST['confirmacion']) && $_POST['confirmacion'] === 'SI CONFIRMO') {
        // Mostrar usuarios actuales
        $sql_users = "SELECT idusuarios, nombre, correo FROM usuarios";
        $result = $conexion->query($sql_users);
        
        echo "<h3>Usuarios actuales en la base de datos:</h3>";
        echo "<ul>";
        while($user = $result->fetch_assoc()) {
            echo "<li>" . $user['nombre'] . " - " . $user['correo'] . "</li>";
        }
        echo "</ul>";
        
        echo "<p><strong>¿Continuar con la eliminación de todos excepto admin@maximaonline.store?</strong></p>";
        echo "<form method='POST' style='margin: 20px 0;'>";
        echo "<input type='hidden' name='confirmado' value='si'>";
        echo "<button type='submit' style='padding: 10px 20px; background: #ff0000; color: white; border: none; border-radius: 5px; font-size: 16px;'>SÍ, ELIMINAR OTROS USUARIOS</button>";
        echo "<a href='index.php' style='padding: 10px 20px; background: #666; color: white; border: none; border-radius: 5px; font-size: 16px; text-decoration: none; margin-left: 10px;'>Cancelar</a>";
        echo "</form>";
    }
} else {
    // Ejecutar limpieza
    $sql_delete = "DELETE FROM usuarios WHERE correo != 'admin@maximaonline.store'";
    
    if($conexion->query($sql_delete)) {
        $deleted = $conexion->affected_rows;
        echo "<h2 style='color: green;'>✅ Limpieza Completada</h2>";
        echo "<p>Se eliminaron <strong>" . $deleted . " usuario(s)</strong></p>";
        echo "<p>Ahora solo admin@maximaonline.store puede administrar, editar y eliminar productos.</p>";
        echo "<p><a href='login.php' style='color: #ff5500; font-weight: bold; font-size: 16px;'>→ Ir a Login</a></p>";
        echo "<p><a href='index.php' style='color: #333; font-weight: bold; font-size: 16px;'>← Volver a Inicio</a></p>";
    } else {
        echo "<h2 style='color: red;'>❌ Error al limpiar: " . $conexion->error . "</h2>";
    }
}

$conexion->close();
?>
