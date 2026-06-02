<?php
include("conexion.php");

$error = "";
$exito = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $correo = $conexion->real_escape_string($_POST['email']);
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);

    // Validar que el correo no exista
    $verificar = $conexion->query("SELECT * FROM usuarios WHERE correo = '$correo'");

    if($verificar && $verificar->num_rows > 0) {
        $error = "Este correo ya está registrado";
    } else {
        // Función para crear filas padre recursivamente si es necesario
        function create_parent_row($conexion, $table) {
            $table = $conexion->real_escape_string($table);
            $err = '';
            // Intentar insertar una fila vacía; capturar excepciones para evitar que aborten el script
            try {
                if($conexion->query("INSERT INTO `".$table."` () VALUES ()")) {
                    return $conexion->insert_id;
                }
            } catch (mysqli_sql_exception $e) {
                $err = $e->getMessage();
            }

            // Si el intento falló por FK, buscar las FKs y crear recursivamente las filas padre
            $checkErr = $err ?: $conexion->error;
            if(stripos($checkErr, 'foreign key') !== false || stripos($checkErr, 'clave extranjera') !== false) {
                try {
                    $q = $conexion->query("SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".$table."' AND REFERENCED_TABLE_NAME IS NOT NULL");
                } catch (mysqli_sql_exception $e) {
                    return false;
                }

                if($q && $q->num_rows > 0) {
                    while($fk = $q->fetch_assoc()) {
                        $parentTable = $fk['REFERENCED_TABLE_NAME'];
                        $parentId = create_parent_row($conexion, $parentTable);
                        if(!$parentId) return false;
                        $col = $fk['COLUMN_NAME'];
                        $sql = "INSERT INTO `".$table."` (`".$conexion->real_escape_string($col)."`) VALUES (".intval($parentId).")";
                        try {
                            if($conexion->query($sql)) return $conexion->insert_id;
                        } catch (mysqli_sql_exception $e) {
                            // continuar intentando con otras FKs si existen
                            continue;
                        }
                    }
                }
            }
            return false;
        }

        // Detectar FKs en usuarios y crear filas padre
        $fkCols = [];
        $fkQ = $conexion->query("SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='usuarios' AND REFERENCED_TABLE_NAME IS NOT NULL");
        $fatal_fk = false;
        if($fkQ && $fkQ->num_rows > 0) {
            while($r = $fkQ->fetch_assoc()) {
                $col = $r['COLUMN_NAME'];
                $parent = $r['REFERENCED_TABLE_NAME'];
                $parentId = create_parent_row($conexion, $parent);
                if($parentId === false) {
                    $error = "No se pudo crear datos relacionados necesarios: " . $conexion->error;
                    $fatal_fk = true;
                    break;
                }
                $fkCols[$col] = $parentId;
            }
        }

        // Preparar INSERT con columnas FK si existen y no hubo error fatal
        if(!$fatal_fk) {
            $cols = ['nombre', 'correo', 'contraseña'];
            $vals = ["'" . $conexion->real_escape_string($nombre) . "'", "'" . $conexion->real_escape_string($correo) . "'", "'" . $conexion->real_escape_string($contraseña) . "'"];
            foreach($fkCols as $c => $v) {
                $cols[] = "`" . $conexion->real_escape_string($c) . "`";
                $vals[] = intval($v);
            }

            $sql = "INSERT INTO usuarios (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")";

            if($conexion->query($sql)) {
                $exito = "¡Registro exitoso! Ahora inicia sesión";
                header("refresh:2;url=login.php");
            } else {
                $error = "Error al registrar: " . $conexion->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro - MAXIMA ONLINE STORE</title>
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
    .exito {
        background: #efe;
        color: #0a0;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        border-left: 4px solid #0a0;
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
    <h2><i class="fas fa-user-plus"></i> Crear Cuenta</h2>
    
    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($exito): ?>
        <div class="exito"><?php echo $exito; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="nombre">Nombre Completo</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="contraseña">Contraseña</label>
            <input type="password" id="contraseña" name="contraseña" required>
        </div>
        
        <button type="submit" class="btn-submit">Registrarse</button>
    </form>
    
    <div class="auth-link">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
    </div>
</div>

</body>
</html>
