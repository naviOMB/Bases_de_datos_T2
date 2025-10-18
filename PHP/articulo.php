<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'autor') {
    header('Location: login.php');
    exit;
}

if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID de artículo inválido');
}
$id_art = (int)$_GET['id'];

$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: '.$mysqli->connect_error);
}

$stmt = $mysqli->prepare("
    SELECT nombre_articulo, resumen_articulo, fecha_publicacion, revisado
      FROM articulo
     WHERE id_articulo = ?
");
$stmt->bind_param('i', $id_art);
$stmt->execute();
$stmt->bind_result($nombre, $resumen, $fecha_pub, $revisado);
if (!$stmt->fetch()) {
    die('Artículo no encontrado');
}
$stmt->close();

$stmt = $mysqli->prepare("
    SELECT COUNT(*) 
      FROM detalle_revision 
     WHERE id_articulo = ? 
       AND fecha_revision IS NOT NULL
");
$stmt->bind_param('i', $id_art);
$stmt->execute();
$stmt->bind_result($num_rev);
$stmt->fetch();
$stmt->close();

if ($num_rev >= 3 && !$revisado) {
    $upd = $mysqli->prepare("
        UPDATE articulo
           SET revisado = 1
         WHERE id_articulo = ?
    ");
    $upd->bind_param('i', $id_art);
    $upd->execute();
    $upd->close();
    $revisado = 1;
}

$hoy_ts = strtotime(date('Y-m-d'));
$pub_ts = $fecha_pub ? strtotime($fecha_pub) : null;
$antes_de_pub = is_null($pub_ts) || $pub_ts > $hoy_ts;

$topics = [];
$stmt = $mysqli->prepare("
    SELECT t.nombre_topico
      FROM detalle_articulo da
      JOIN topico t ON da.id_topico = t.id_topico
     WHERE da.id_articulo = ?
");
$stmt->bind_param('i', $id_art);
$stmt->execute();
$stmt->bind_result($tname);
while ($stmt->fetch()) {
    $topics[] = $tname;
}
$stmt->close();

$revisores = [];
$stmt = $mysqli->prepare("
    SELECT m.rut_miembro, m.nombre_miembro
      FROM detalle_revision dr
      JOIN miembro m ON dr.rut_miembro = m.rut_miembro
     WHERE dr.id_articulo = ?
");
$stmt->bind_param('i', $id_art);
$stmt->execute();
$stmt->bind_result($rut_rev, $nom_rev);
while ($stmt->fetch()) {
    $revisores[] = ['rut'=>$rut_rev,'nombre'=>$nom_rev];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Artículo – GESCON</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f9f9f9;margin:0}
    header{background:#003366;color:#fff;padding:15px 30px;
           display:flex;justify-content:space-between;align-items:center}
    header h1{margin:0;font-size:1.5em}
    .volver{background:#fff;color:#003366;border:1px solid #003366;
            padding:8px 16px;border-radius:5px;text-decoration:none;font-weight:bold}
    .container{padding:30px;max-width:900px;margin:0 auto}
    .section{margin-bottom:40px}
    .section h2{color:#003366;margin-bottom:15px}
    .info p{margin:5px 0}
    .info strong{display:inline-block;width:150px}
    .btn-edit {
      display:inline-block;margin-top:15px;
      background:#0066cc;color:#fff;padding:8px 16px;border:none;border-radius:4px;
      text-decoration:none;font-weight:bold;
    }
    .btn-edit:hover{background:#0055aa}
    table{width:100%;border-collapse:collapse;margin-top:15px}
    th,td{border:1px solid #ccc;padding:12px;vertical-align:middle}
    th{background:#003366;color:#fff;text-align:left}
    .btn-detail{
      background:#003366;color:#fff;padding:6px 12px;border:none;border-radius:4px;
      cursor:pointer;
    }
    .btn-detail[disabled]{background:#aaa;cursor:default}
  </style>
</head>
<body>

  <header>
    <h1>Artículo</h1>
    <a href="autor_home.php" class="volver">Volver a Home</a>
  </header>

  <div class="container">
    <div class="section info">
      <h2>Información del artículo</h2>
      <p><strong>Nombre:</strong> <?= htmlspecialchars($nombre) ?></p>
      <p><strong>Resumen:</strong> <?= htmlspecialchars($resumen) ?></p>
      <p><strong>Fecha de publicación:</strong>
        <?= $fecha_pub ? date('Y-m-d', strtotime($fecha_pub)) : '-' ?>
      </p>
      <p><strong>Tópicos:</strong>
        <?= $topics ? htmlspecialchars(implode(', ', $topics)) : '-' ?>
      </p>

      <?php if ($antes_de_pub): ?>
        <?php if (!$revisado): ?>
          <a href="editar_articulo.php?id=<?= $id_art ?>" class="btn-edit">Editar</a>
        <?php endif ?>
      <?php endif ?>
    </div>

    <div class="section">
      <h2>Revisores</h2>
      <table>
        <thead>
          <tr>
            <th>Revisor</th>
            <th>Detalle</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($revisores): ?>
            <?php foreach ($revisores as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['nombre']) ?></td>
                <td>
                  <button
                    class="btn-detail"
                    type="button"
                    <?= (!$antes_de_pub && $revisado)
                        ? "onclick=\"location.href='detalle_revision.php?rut="
                          .urlencode($r['rut'])
                          ."&id={$id_art}'\""
                        : 'disabled' ?>>
                    Detalle
                  </button>
                </td>
              </tr>
            <?php endforeach ?>
          <?php else: ?>
            <tr>
              <td colspan="2" style="text-align:center">— Sin revisores asignados —</td>
            </tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
