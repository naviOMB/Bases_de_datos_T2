<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - GESCON</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url("../img/fondo_login.jpg"); 
            background-size: cover;
            background-position: center;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
            width: 300px;
        }


        input[type="text"], input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #aaa;
        }

        .roles {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 0 10px;
        }

        .roles label {
            font-size: 14px;
        }

        .boton {
            background-color: #003366;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
        }

        .register {
            margin-top: 15px;
            font-size: 14px;
        }

        .register a {
            color: #003366;
            text-decoration: none;
        }

        .register a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h1>LOGIN</h1>

    <form action="procesar_login.php" method="POST">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>

        <div class="roles">
            <label><input type="radio" name="rol" value="autor" required> Autor</label>
            <label><input type="radio" name="rol" value="revisor"> Revisor</label>
            <label><input type="radio" name="rol" value="admin"> Admin</label>
        </div>


        <button type="submit" class="boton">Entrar</button>

        <div class="register">
            ¿No tienes cuenta? <a href="registro.php">Regístrate</a><br><br>
            <a href="index.php" style="color: #003366; text-decoration: none;">← Volver al inicio</a>
        </div>
    </form>

</body>
</html>
