<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

if (empty($_GET['rut']) || empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('Parámetros inválidos.');
}
$rut_rev = $_GET['rut'];
$id_art  = (int)$_GET['id'];

$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

$stmt = $mysqli->prepare("SELECT nombre_articulo FROM articulo WHERE id_articulo = ?");
$stmt->bind_param('i', $id_art);
$stmt->execute();
$stmt->bind_result($nombre_art);
if (!$stmt->fetch()) {
    die('Artículo no encontrado.');
}
$stmt->close();

$stmt = $mysqli->prepare("
    SELECT 
      calidad_tecnica,
      originalidad,
      valoracion_global,
      argumento_valoracion,
      comentario_revision,
      fecha_revision
    FROM detalle_revision
   WHERE id_articulo = ? AND rut_miembro = ?
");
$stmt->bind_param('is', $id_art, $rut_rev);
$stmt->execute();
$stmt->bind_result(
    $calidad,
    $originalidad,
    $global,
    $argumento,
    $comentario,
    $fecha_rev
);
if (!$stmt->fetch()) {
    die('Revisión no encontrada.');
}
$stmt->close();

$home = ($_SESSION['rol'] === 'autor') ? 'autor_home.php' : 'revisor_home.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalle de Revisión – GESCON</title>
  <style>
    body { font-family: Arial,sans-serif; background:#f9f9f9; margin:0; }
    header {
      background:#003366; color:#fff;
      display:flex; justify-content:space-between; align-items:center;
      padding:15px 30px;
    }
    header h1 { margin:0; text-transform:uppercase; font-size:1.2em;}
    .volver, .home-btn {
      background:#fff; color:#003366; border:1px solid #003366;
      padding:8px 16px; border-radius:5px; text-decoration:none; font-weight:bold;
      margin-left:10px;
    }
    .volver:hover, .home-btn:hover { background:#002244; color:#fff; }
    .container {
      max-width:600px; margin:30px auto;
      background:#fff; padding:30px;
      border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1);
    }
    .container h2 { color:#003366; margin-top:0; }
    label {
      display:block; margin-top:15px; font-weight:bold; color:#333;
    }
    input[type="number"], textarea {
      width:100%; padding:10px; margin-top:5px;
      border:1px solid #ccc; border-radius:5px;
      background:#f0f0f0; resize:none; font-family:inherit;
    }
    input[disabled], textarea[disabled] {
      color:#333;
    }
  </style>
</head>
<body>

  <header>
    <h1>Consulta de Revisión</h1>
    <div>
      <a href="articulo.php?id=<?= $id_art ?>" class="volver">← Volver</a>
      <a href="<?= $home ?>" class="home-btn">Inicio</a>
    </div>
  </header>

  <div class="container">
    <h2>Artículo: <?= htmlspecialchars($nombre_art) ?></h2>
    <p><strong>Fecha de revisión:</strong> <?= htmlspecialchars($fecha_rev) ?></p>

    <label>Calidad Técnica:</label>
    <input type="number" value="<?= $calidad ?>" min="1" max="10" disabled>

    <label>Originalidad:</label>
    <input type="number" value="<?= $originalidad ?>" min="1" max="10" disabled>

    <label>Valoración Global:</label>
    <input type="number" value="<?= $global ?>" min="1" max="10" disabled>

    <label>Argumentos Valoración Global:</label>
    <textarea rows="4" disabled><?= htmlspecialchars($argumento) ?></textarea>

    <label>Comentarios a autores:</label>
    <textarea rows="4" disabled><?= htmlspecialchars($comentario) ?></textarea>
  </div>

</body>
</html>
