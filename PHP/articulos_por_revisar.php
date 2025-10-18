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

$sql = "
  SELECT
    a.id_articulo,
    a.nombre_articulo,
    a.fecha_publicacion,
    GROUP_CONCAT(DISTINCT m_aut.nombre_miembro SEPARATOR ', ') AS autores
  FROM articulo a
  INNER JOIN detalle_revision dr
    ON a.id_articulo = dr.id_articulo
   AND dr.rut_miembro = ?
   AND dr.fecha_revision IS NULL
  INNER JOIN detalle_publicacion dp
    ON a.id_articulo = dp.id_articulo
  INNER JOIN miembro m_aut
    ON dp.rut_miembro = m_aut.rut_miembro
  GROUP BY a.id_articulo, a.nombre_articulo, a.fecha_publicacion
  ORDER BY a.fecha_publicacion DESC
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $rut_revisor);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Artículos por Revisar – GESCON</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 0; }
    header {
      background: #003366; color: #fff;
      padding: 15px 30px;
      display: flex; justify-content: space-between; align-items: center;
    }
    .volver {
      background: #fff; color: #003366;
      border: 1px solid #003366;
      padding: 8px 16px; border-radius: 5px;
      text-decoration: none; font-weight: bold;
    }
    .container { padding: 30px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    colgroup col { }
    colgroup col.action-col {
      width: 1%;
      white-space: nowrap;
    }
    th, td {
      border: 1px solid #ccc; padding: 12px; vertical-align: top;
    }
    th {
      background: #003366; color: #fff; text-align: left;
    }
    th.action, td.action {
      text-align: center;
    }
    td.authors { white-space: normal; line-height: 1.4em; }
    .btn-review {
      background: #2980b9; color: #fff;
      border: none; border-radius: 4px;
      padding: 8px 16px; cursor: pointer;
      text-decoration: none; font-size: 0.9em;
      white-space: nowrap;
    }
  </style>
</head>
<body>

  <header>
    <h1>Artículos por Revisar</h1>
    <a href="revisor_home.php" class="volver">← Volver a Home</a>
  </header>

  <div class="container">
    <?php if ($result->num_rows === 0): ?>
      <p>No tienes artículos pendientes de revisión.</p>
    <?php else: ?>
      <table>
        <colgroup>
          <col>
          <col>
          <col>
          <col class="action-col">
        </colgroup>
        <thead>
          <tr>
            <th>Título Artículo</th>
            <th>Fecha Publicación</th>
            <th>Autores</th>
            <th class="action">Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['nombre_articulo']) ?></td>
              <td>
                <?= $row['fecha_publicacion']
                    ? date('d/m/Y', strtotime($row['fecha_publicacion']))
                    : '-' ?>
              </td>
              <td class="authors">
                <?= implode(
                     '<br>',
                     array_map('htmlspecialchars', explode(', ', $row['autores']))
                   ) ?>
              </td>
              <td class="action">
                <a href="revisar_articulo.php?id=<?= urlencode($row['id_articulo']) ?>"
                   class="btn-review">Revisar</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</body>
</html>
