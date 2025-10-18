<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'revisor') {
    header('Location: login.php');
    exit;
}

$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

$userid = $_SESSION['usuario'];
$stmt = $mysqli->prepare("SELECT rut_miembro FROM miembro WHERE userid_miembro = ?");
$stmt->bind_param('s', $userid);
$stmt->execute();
$stmt->bind_result($rut_revisor);
$stmt->fetch();
$stmt->close();

$article_id = isset($_GET['id']) 
    ? (int)$_GET['id'] 
    : (int)$_POST['id_articulo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calidad  = intval($_POST['calidad']);
    $original = intval($_POST['originalidad']);
    $global   = intval($_POST['valoracion']);
    $arg      = trim($_POST['arg_global']);
    $coment   = trim($_POST['comentarios']);

    $upd = $mysqli->prepare("
        UPDATE detalle_revision
           SET calidad_tecnica       = ?,
               originalidad          = ?,
               valoracion_global     = ?,
               argumento_valoracion  = ?,
               comentario_revision   = ?,
               fecha_revision        = CURDATE()
         WHERE id_articulo = ?
           AND rut_miembro  = ?
    ");
    $upd->bind_param('iiissis',
        $calidad, $original, $global,
        $arg, $coment,
        $article_id, $rut_revisor
    );
    $upd->execute();
    $upd->close();

    $stmt2 = $mysqli->prepare("
        SELECT COUNT(*) 
          FROM detalle_revision 
         WHERE id_articulo = ?
           AND valoracion_global IS NOT NULL
    ");
    $stmt2->bind_param('i', $article_id);
    $stmt2->execute();
    $stmt2->bind_result($numCompletas);
    $stmt2->fetch();
    $stmt2->close();

    if ($numCompletas >= 3) {
        $upd2 = $mysqli->prepare("
            UPDATE articulo
               SET revisado = 1,
                   fecha_publicacion = CURDATE()
             WHERE id_articulo = ?
        ");
        $upd2->bind_param('i', $article_id);
        $upd2->execute();
        $upd2->close();
    }

    $_SESSION['flash'] = 'Revisión guardada correctamente.';
    header("Location: articulos_por_revisar.php");
    exit;
}

$stmt = $mysqli->prepare("SELECT nombre_articulo, fecha_publicacion FROM articulo WHERE id_articulo = ?");
$stmt->bind_param('i', $article_id);
$stmt->execute();
$stmt->bind_result($article_name, $pub_date);
if (!$stmt->fetch()) {
    die('Artículo no encontrado');
}
$stmt->close();

$hoy = date('Y-m-d');
$puedeVer = is_null($pub_date) || $pub_date <= $hoy;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>REVISION – Artículo #<?= htmlspecialchars($article_id) ?></title>
  <style>
    body {margin:0;padding:0;font-family:Arial,sans-serif;background:#f9f9f9}
    header{background:#003366;color:#fff;display:flex;
           justify-content:space-between;align-items:center;
           padding:15px 30px}
    header h1{margin:0;text-transform:uppercase;font-size:1.5em}
    .volver{background:#fff;color:#003366;
            border:1px solid #003366;padding:8px 16px;
            border-radius:5px;text-decoration:none;
            font-weight:bold}
    .container{padding:30px}
    .form-container{background:#fff;padding:30px;
                    max-width:600px;margin:0 auto;
                    border-radius:10px;
                    box-shadow:0 2px 10px rgba(0,0,0,0.1)}
    .form-container h2{margin-top:0;color:#003366}
    label{display:block;margin-top:15px;font-weight:bold;color:#333}
    input[type="number"],textarea {
      width:100%;padding:10px;margin-top:5px;
      border:1px solid #aaa;border-radius:5px;
      box-sizing:border-box;font-family:inherit;
      font-size:1em
    }
    textarea{resize:vertical}
    button.submit-btn {
      margin-top:20px;background:#003366;color:#fff;
      border:none;padding:12px 20px;border-radius:5px;
      cursor:pointer;font-size:1em
    }
    button.submit-btn:hover{background:#002244}
    .alert {background:#ffc;border:1px solid #cc9;
            padding:15px;border-radius:5px;margin-bottom:20px}
  </style>
</head>
<body>

  <header>
    <h1>REVISION</h1>
    <a href="articulos_por_revisar.php" class="volver">← Volver</a>
  </header>

  <div class="container">
    <?php if (!$puedeVer): ?>
      <div class="alert">
        La fecha de publicación (<strong><?= htmlspecialchars($pub_date) ?></strong>)
        aún no ha llegado. No puedes revisar todavía.
      </div>
    <?php endif ?>

    <div class="form-container">
      <h2>Artículo: <?= htmlspecialchars($article_name) ?></h2>
      <form method="POST">
        <input type="hidden" name="id_articulo" value="<?= $article_id ?>">

        <label for="calidad">Calidad Técnica:</label>
        <input type="number" id="calidad" name="calidad" min="1" max="10" 
               <?= $puedeVer ? 'required' : 'disabled' ?>>

        <label for="originalidad">Originalidad:</label>
        <input type="number" id="originalidad" name="originalidad" min="1" max="10" 
               <?= $puedeVer ? 'required' : 'disabled' ?>>

        <label for="valoracion">Valoración Global:</label>
        <input type="number" id="valoracion" name="valoracion" min="1" max="10" 
               <?= $puedeVer ? 'required' : 'disabled' ?>>

        <label for="arg_global">Argumentos Valoración Global:</label>
        <textarea id="arg_global" name="arg_global" rows="4"
                  <?= $puedeVer ? 'required' : 'disabled' ?>></textarea>

        <label for="comentarios">Comentarios a autores:</label>
        <textarea id="comentarios" name="comentarios" rows="4"
                  <?= $puedeVer ? 'required' : 'disabled' ?>></textarea>

        <?php if ($puedeVer): ?>
          <button type="submit" class="submit-btn">Guardar Revisión</button>
        <?php endif ?>
      </form>
    </div>
  </div>

</body>
</html>
