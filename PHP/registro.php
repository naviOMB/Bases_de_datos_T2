<?php
$conexion = new mysqli("localhost", "root", "", "test");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $verificar = $conexion->prepare("SELECT * FROM miembro WHERE rut_miembro = ? OR nombre_miembro = ? OR email_miembro = ? OR userid_miembro = ?");
    $verificar->bind_param("ssss", $rut, $nombre, $email, $usuario);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows > 0) {
        echo "<script>alert('Ya existe un miembro con ese RUT, nombre, correo o nombre de usuario'); window.history.back();</script>";
    } else {
        $stmt = $conexion->prepare("INSERT INTO miembro (rut_miembro, nombre_miembro, email_miembro, userid_miembro, password_miembro, es_autor, es_revisor, es_admin) VALUES (?, ?, ?, ?, ?, 1, 0, 0)");
        $stmt->bind_param("sssss", $rut, $nombre, $email, $usuario, $password);
        if ($stmt->execute()) {
            echo "<script>
                    alert('¡Registro exitoso! Se ha enviado un correo de confirmación para verificar tu cuenta.');
                    window.location.href='login.php';
                </script>";

        } else {
            echo "<script>alert('Error al registrar: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }

    $verificar->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - GESCON</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url("img/fondo_login.jpg");
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        h1 {
            color: #003366;
        }

        form {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.2);
            text-align: center;
            width: 320px;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #aaa;
        }

        .boton {
            background-color: #003366;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
        }

        .volver {
            margin-top: 15px;
            font-size: 14px;
        }

        .volver a {
            color: #003366;
            text-decoration: none;
        }

        .volver a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h1>REGISTRO</h1>

    <form action="#" method="POST">
        <input type="text" name="rut" placeholder="RUT (ej: 12345678-9)" required>
        <input type="text" name="nombre" placeholder="Nombre completo" required>
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="text" name="usuario" placeholder="Nombre de usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>

        <button type="submit" class="boton">Registrarse</button>

        <div class="volver">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </div>
    </form>

</body>
</html>
