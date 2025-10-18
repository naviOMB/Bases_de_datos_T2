<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Jefe de Comité – GESCON</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        header {
            background-color: #003366;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }
        header h1 {
            margin: 0;
        }
        .volver-btn, .logout-btn {
            background-color: #ffffff;
            color: #003366;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            border: 1px solid #003366;
            transition: background-color 0.3s, color 0.3s;
            margin-left: 10px;
        }
        .volver-btn:hover, .logout-btn:hover {
            background-color: #003366;
            color: white;
        }
        .search-container {
            display: flex;
            justify-content: center;
            margin: 30px auto;
            max-width: 600px;
        }
        .search-container input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #aaa;
            border-radius: 5px 0 0 5px;
        }
        .search-container button {
            padding: 10px 20px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }
        .acciones {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin: 30px auto;
            max-width: 900px;
        }
        .acciones a {
            background-color: #0056b3;
            color: white;
            padding: 20px 25px;
            text-decoration: none;
            border-radius: 6px;
            width: 200px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.3s;
            font-size: 1.1em;
        }
        .acciones a:hover {
            background-color: #003f7f;
        }
    </style>
</head>
<body>

    <header>
        <h1>Bienvenido <?= htmlspecialchars($_SESSION['nombre_miembro']) ?> (Jefe de Comité)</h1>
        <div>
            <a href="index.php" class="volver-btn">Volver al inicio</a>
            <a href="logout.php" class="logout-btn">Cerrar sesión</a>
        </div>
    </header>

    <div class="acciones">
        <a href="all_articulos.php">Artículos</a>
        <a href="gestionar_asignacion_revisores.php">Asignar Revisores</a>
        <a href="gestion_revisiones.php">Gestionar Revisores</a>
        <a href="cuenta.php">Cuenta</a>
    </div>

</body>
</html>
