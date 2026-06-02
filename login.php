<?php
session_start();
include("conexion.php");

$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conexion->real_escape_string($_POST['email']);
    $contraseña = $_POST['contraseña'];
    
    $sql = "SELECT * FROM usuarios WHERE correo = '$email'";
    $resultado = $conexion->query($sql);
    
    if($resultado && $resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        
        if(password_verify($contraseña, $usuario['contraseña'])) {
            $_SESSION['usuario_id'] = $usuario['idusuarios'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['correo'];
            
            header("Location: admin_panel.php");
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Email no registrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar Sesión - MAXIMA ONLINE STORE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="estilos.css">
<style>
    .auth-container {
        max-width: 400px;
        margin: 50px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .auth-container h2 {
        color: #000000;
        margin-bottom: 20px;
        text-align: center;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: bold;
    }
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
    .form-group input:focus {
        outline: none;
        border-color: #ff5500;
        box-shadow: 0 0 5px rgba(255,165,0,0.3);
    }
    .btn-submit {
        width: 100%;
        padding: 12px;
        background: #000000;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
    }
    .btn-submit:hover {
        background: #004080;
    }
    .error {
        background: #fee;
        color: #c00;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        border-left: 4px solid #c00;
    }
    .auth-link {
        text-align: center;
        margin-top: 15px;
    }
    .auth-link a {
        color: #ff5500;
        text-decoration: none;
    }
    .auth-link a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="auth-container">
    <h2><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h2>
    
    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="contraseña">Contraseña</label>
            <input type="password" id="contraseña" name="contraseña" required>
        </div>
        
        <button type="submit" class="btn-submit">Iniciar Sesión</button>
    </form>
    
    <div class="auth-link">
        ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
    </div>
</div>

</body>
</html>
