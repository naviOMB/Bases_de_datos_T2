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

$search = '';
if (!empty($_GET['search'])) {
    $search = trim($_GET['search']);
}

$sql = "
  SELECT
    a.id_articulo,
    a.nombre_articulo,
    a.fecha_publicacion,
    dr.fecha_revision
  FROM articulo a
  JOIN detalle_revision dr 
    ON a.id_articulo = dr.id_articulo
  WHERE dr.rut_miembro = ?
    AND dr.fecha_revision IS NOT NULL
";

if ($search !== '') {
    $sql .= " AND a.nombre_articulo LIKE ?";
}
$sql .= " ORDER BY dr.fecha_revision DESC";

$stmt = $mysqli->prepare($sql);

if ($search !== '') {
    $like = "%{$search}%";
    $stmt->bind_param('ss', $rut_revisor, $like);
} else {
    $stmt->bind_param('s', $rut_revisor);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Artículos Evaluados – GESCON</title>
  <style>
    body { font-family:Arial,sans-serif; background:#f4f4f4; margin:0; }
    header { background:#003366; color:white; display:flex; justify-content:space-between; align-items:center; padding:15px 30px; }
    header h1{ margin:0; }
    .volver-btn, .logout-btn { background:#fff; color:#003366; padding:10px 20px; text-decoration:none; border:1px solid #003366; border-radius:5px; font-weight:bold; transition:.3s; }
    .volver-btn:hover, .logout-btn:hover { background:#002244; color:#fff; }
    .container{ padding:30px; max-width:900px; margin:30px auto; background:#fff; border-radius:8px; }
    .search-bar { margin-bottom:20px; display:flex; }
    .search-bar input[type="text"] {
      flex:1; padding:8px; border:1px solid #ccc; border-radius:4px 0 0 4px;
      font-size:1em;
    }
    .search-bar button {
      background:#003366; color:#fff; border:none; padding:8px 16px;
      border-radius:0 4px 4px 0; cursor:pointer; transition:.2s;
    }
    .search-bar button:hover { background:#002244; }
    table { width:100%; border-collapse:collapse; margin-top:0; }
    th, td { padding:12px; border:1px solid #ccc; text-align:left; }
    th { background:#003366; color:#fff; }
    a.link-art { color:#003366; text-decoration:none; font-weight:bold; }
    a.link-art:hover { text-decoration:underline; }
  </style>
</head>
<body>

  <header>
    <h1>Mis Artículos Evaluados</h1>
    <div>
      <a href="revisor_home.php" class="volver-btn">Volver al inicio</a>
      <a href="logout.php" class="logout-btn">Cerrar sesión</a>
    </div>
  </header>

  <div class="container">
    <form method="GET" action="articulos_evaluados.php" class="search-bar">
      <input
        type="text"
        name="search"
        placeholder="Buscar artículo por título..."
        value="<?= htmlspecialchars($search) ?>"
      >
      <button type="submit">Buscar</button>
    </form>
    
    <?php if ($result && $result->num_rows): ?>
      <table>
        <thead>
          <tr>
            <th>Título</th>
            <th>Fecha publicación</th>
            <th>Fecha de mi revisión</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td>
                <a
                  href="detalle_revision.php?rut=<?= urlencode($rut_revisor) ?>&amp;id=<?= $row['id_articulo'] ?>"
                  class="link-art"
                >
                  <?= htmlspecialchars($row['nombre_articulo']) ?>
                </a>
              </td>
              <td><?= htmlspecialchars($row['fecha_publicacion']) ?></td>
              <td><?= htmlspecialchars($row['fecha_revision']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No has evaluado ningún artículo todavía<?= $search!=='' ? ' (o no hay coincidencias para “'.htmlspecialchars($search).'”)' : '' ?>.</p>
    <?php endif; ?>
  </div>

</body>
</html>
