<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['article'])) {
    die('Falta ID de artículo');
}
$id_art = (int)$_GET['article'];

$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: '.$mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['rut'])) {
    $rut = $_POST['rut'];
    $ins = $mysqli->prepare("
        INSERT INTO detalle_revision (rut_miembro, id_articulo)
        VALUES (?, ?)
    ");
    $ins->bind_param('si', $rut, $id_art);
    $ins->execute();
    $ins->close();
    header("Location: ".$_SERVER['PHP_SELF']."?article=".$id_art);
    exit;
}

$t = $mysqli->prepare("
    SELECT id_topico
      FROM detalle_articulo
     WHERE id_articulo = ?
");
$t->bind_param('i', $id_art);
$t->execute();
$resT = $t->get_result();
$topicos_art = [];
while($r=$resT->fetch_assoc()) {
    $topicos_art[] = (int)$r['id_topico'];
}
$t->close();

$compatibles = [];
if ($topicos_art) {
    $in = implode(',', $topicos_art);
    $sql = "
      SELECT DISTINCT m.rut_miembro, m.nombre_miembro
        FROM miembro m
        JOIN detalle_topico dt ON dt.rut_miembro = m.rut_miembro
       WHERE m.es_revisor = 1
         AND dt.id_topico IN ($in)
         AND m.rut_miembro NOT IN (
             SELECT rut_miembro
               FROM detalle_revision
              WHERE id_articulo = $id_art
         )
       ORDER BY m.nombre_miembro
    ";
    $q = $mysqli->query($sql);
    while($r = $q->fetch_assoc()) {
        $compatibles[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Asignar Revisores – Artículo #<?= $id_art ?></title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
    header {
      background: #003366; color: #fff;
      padding: 15px 30px; display: flex;
      justify-content: space-between; align-items: center;
    }
    .volver {
      background: #fff; color: #003366;
      border: 1px solid #003366;
      padding: 8px 16px; border-radius: 5px;
      text-decoration: none; font-weight: bold;
    }
    .container { padding: 30px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td {
      border: 1px solid #ccc; padding: 10px;
      vertical-align: top;
    }
    th { background: #003366; color: #fff; text-align: left; }
    td.topics, td.assigned {
      white-space: normal;
      line-height: 1.4em;
    }
    .btn-assign {
      background: #27ae60; color: #fff;
      border: none; border-radius: 4px;
      padding: 6px 12px; cursor: pointer;
      font-size: 0.9em;
    }
    .btn-assign:disabled {
      background: #aaa; cursor: not-allowed;
    }
  </style>
</head>
<body>
  <header>
    <h1>Asignar revisores al artículo #<?= $id_art ?></h1>
    <a href="gestionar_asignacion_revisores.php" class="volver">← Volver</a>
  </header>
  <div class="container">
    <?php if (empty($compatibles)): ?>
      <p>No hay revisores compatibles (o ya están todos asignados).</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Miembro</th>
            <th>Tópicos</th>
            <th>Artículos asignados</th>
            <th>Asignar</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($compatibles as $c): 
            $t2 = $mysqli->prepare("
              SELECT t.nombre_topico
                FROM topico t
                JOIN detalle_topico dt ON dt.id_topico = t.id_topico
               WHERE dt.rut_miembro = ?
               ORDER BY t.nombre_topico
            ");
            $t2->bind_param('s', $c['rut_miembro']);
            $t2->execute();
            $res2 = $t2->get_result();
            $topics = [];
            while($r2=$res2->fetch_assoc()) $topics[] = $r2['nombre_topico'];
            $t2->close();

            $a2 = $mysqli->prepare("
              SELECT a.nombre_articulo
                FROM articulo a
                JOIN detalle_revision dr ON dr.id_articulo = a.id_articulo
               WHERE dr.rut_miembro = ?
               ORDER BY a.nombre_articulo
            ");
            $a2->bind_param('s', $c['rut_miembro']);
            $a2->execute();
            $resA = $a2->get_result();
            $arts = [];
            while($rA=$resA->fetch_assoc()) $arts[] = $rA['nombre_articulo'];
            $a2->close();
          ?>
          <tr>
            <td><?= htmlspecialchars($c['nombre_miembro']) ?></td>
            <td class="topics">
              <?= $topics
                  ? implode('<br>', array_map('htmlspecialchars',$topics))
                  : '-' ?>
            </td>
            <td class="assigned">
              <?= $arts
                  ? implode('<br>', array_map('htmlspecialchars',$arts))
                  : '-' ?>
            </td>
            <td>
              <form method="POST" style="margin:0">
                <input type="hidden" name="rut" value="<?= htmlspecialchars($c['rut_miembro']) ?>">
                <button type="submit" class="btn-assign">
                  Asignar
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
