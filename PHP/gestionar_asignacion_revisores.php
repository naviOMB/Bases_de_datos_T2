<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: '.$mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='remove') {
    $id_art = (int)$_POST['id_articulo'];
    $rut    = $_POST['rut_revisor'];
    $del = $mysqli->prepare("
      DELETE FROM detalle_revision
       WHERE id_articulo = ? AND rut_miembro = ?
    ");
    $del->bind_param('is',$id_art,$rut);
    $del->execute();
    $del->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$sql = "
  SELECT
    a.id_articulo,
    a.nombre_articulo,
    GROUP_CONCAT(DISTINCT m_aut.nombre_miembro ORDER BY m_aut.nombre_miembro SEPARATOR ', ') AS autores,
    GROUP_CONCAT(DISTINCT t.nombre_topico SEPARATOR ', ')               AS topicos,
    GROUP_CONCAT(DISTINCT m_rev.nombre_miembro ORDER BY m_rev.nombre_miembro SEPARATOR ', ') AS revisores,
    GROUP_CONCAT(DISTINCT m_rev.rut_miembro ORDER BY m_rev.nombre_miembro SEPARATOR ',')      AS revisores_ruts
  FROM articulo a
  LEFT JOIN detalle_publicacion dp ON a.id_articulo = dp.id_articulo
  LEFT JOIN miembro m_aut          ON dp.rut_miembro = m_aut.rut_miembro
  LEFT JOIN detalle_articulo da    ON a.id_articulo = da.id_articulo
  LEFT JOIN topico t               ON da.id_topico = t.id_topico
  LEFT JOIN detalle_revision dr    ON a.id_articulo = dr.id_articulo
  LEFT JOIN miembro m_rev          ON dr.rut_miembro = m_rev.rut_miembro
  GROUP BY a.id_articulo, a.nombre_articulo
  ORDER BY a.id_articulo
";
$res = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin – Asignar Revisores</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; }
    header {
      background:#003366; color:#fff; padding:15px 30px;
      display:flex; justify-content:space-between; align-items:center;
    }
    .volver-btn {
      background:#fff; color:#003366; padding:10px 20px;
      text-decoration:none; border:1px solid #003366;
      border-radius:5px; font-weight:bold;
      transition: background-color .3s, color .3s;
    }
    .volver-btn:hover {
      background:#003366; color:#fff;
    }
    .container{ padding:30px; }
    table{ width:100%; border-collapse:collapse; margin-top:20px; }
    th,td{ border:1px solid #ccc; padding:8px; vertical-align:top; }
    th{ background:#003366; color:#fff; }
    .rev-list {
      display:grid;
      grid-template-columns:1fr auto;
      row-gap:6px;
      align-items:center;
    }
    .rev-list span {
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
      padding-right:8px;
    }
    .rev-list form { margin:0; }
    .btn-rem {
      background:#c0392b; color:#fff; border:none;
      width:24px; height:24px; border-radius:3px;
      cursor:pointer; line-height:1; font-weight:bold;
    }
    .btn-assign {
      display:inline-block; margin:0; padding:6px 12px;
      background:#2980b9; color:#fff; border:none;
      border-radius:4px; text-decoration:none;
      font-size:0.9em; cursor:pointer;
    }
  </style>
</head>
<body>
  <header>
    <h1>Gestión de Artículos / Revisores</h1>
    <a href="admin_home.php" class="volver-btn">← Volver</a>
  </header>
  <div class="container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Título</th>
          <th>Autores</th>
          <th>Tópicos</th>
          <th>Revisores</th>
          <th>Asignar</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
          <?php
            $names = $row['revisores']
                   ? explode(', ', $row['revisores'])
                   : [];
            $ruts  = $row['revisores_ruts']
                   ? explode(',', $row['revisores_ruts'])
                   : [];
            $numRev = count($names);

            $safeTitle = htmlspecialchars($row['nombre_articulo']);
            if ($numRev <= 1) {
              $titleHtml = "<span style=\"color:red;\">{$safeTitle} (faltan revisores)</span>";
            } else {
              $titleHtml = $safeTitle;
            }
          ?>
        <tr>
          <td><?= $row['id_articulo'] ?></td>
          <td><?= $titleHtml ?></td>
          <td>
            <?php
              if ($row['autores']) {
                echo implode(
                  '<br>',
                  array_map('htmlspecialchars', explode(', ', $row['autores']))
                );
              } else {
                echo '-';
              }
            ?>
          </td>
          <td>
            <?php
              if ($row['topicos']) {
                echo implode(
                  '<br>',
                  array_map('htmlspecialchars', explode(',',$row['topicos']))
                );
              } else {
                echo '-';
              }
            ?>
          </td>
          <td>
            <div class="rev-list">
              <?php foreach($names as $i => $n): ?>
                <span><?= htmlspecialchars($n) ?></span>
                <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                  <input type="hidden" name="action"       value="remove">
                  <input type="hidden" name="id_articulo"  value="<?= $row['id_articulo'] ?>">
                  <input type="hidden" name="rut_revisor"  value="<?= htmlspecialchars($ruts[$i] ?? '') ?>">
                  <button type="submit" class="btn-rem">−</button>
                </form>
              <?php endforeach; ?>
            </div>
          </td>
          <td>
            <a href="gestionar_asignacion_revisores_articulo.php?article=<?= $row['id_articulo'] ?>"
               class="btn-assign">Asignar…</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
