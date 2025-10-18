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

$titlesRes    = $mysqli->query("SELECT DISTINCT nombre_articulo FROM articulo ORDER BY nombre_articulo");
$reviewersRes = $mysqli->query("
    SELECT DISTINCT m.nombre_miembro
      FROM miembro m
      JOIN detalle_revision dr ON m.rut_miembro = dr.rut_miembro
    ORDER BY m.nombre_miembro
");
$authorsRes = $mysqli->query("
    SELECT DISTINCT m.nombre_miembro
      FROM miembro m
      JOIN detalle_publicacion dp ON m.rut_miembro = dp.rut_miembro
    ORDER BY m.nombre_miembro
");

$topicsRes = $mysqli->query("SELECT nombre_topico FROM topico ORDER BY nombre_topico");

$where  = [];
$params = [];
$types  = '';

if (!empty($_GET['title'])) {
    $where[]  = 'nombre_articulo LIKE ?';
    $params[] = '%'.$_GET['title'].'%';
    $types   .= 's';
}

if (!empty($_GET['topic'])) {
    $where[]  = 'Topicos LIKE ?';
    $params[] = '%'.$_GET['topic'].'%';
    $types   .= 's';
}

if (!empty($_GET['date'])) {
    $where[]  = 'fecha_de_publicacion = ?';
    $params[] = $_GET['date'];
    $types   .= 's';
}

if (!empty($_GET['reviewer'])) {
    $where[]  = 'Revisores LIKE ?';
    $params[] = '%'.$_GET['reviewer'].'%';
    $types   .= 's';
}

if (!empty($_GET['author'])) {
    $where[]  = 'Autores LIKE ?';
    $params[] = '%'.$_GET['author'].'%';
    $types   .= 's';
}

$sql = "
  SELECT
    nombre_articulo,
    Topicos               AS topicos,
    fecha_de_publicacion,
    Autores               AS autores,
    Revisores             AS revisores,
    Estado                AS revisado
  FROM todos_los_articulos
";

if ($where) {
    $sql .= ' WHERE '.implode(' AND ', $where);
}
$sql .= " ORDER BY nombre_articulo";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Todos los Artículos – GESCON</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;}
    header{background:#003366;color:#fff;display:flex;justify-content:space-between;align-items:center;padding:15px 30px;}
    header h1{margin:0;}
    .volver-btn,.logout-btn{
      background:#fff;color:#003366;padding:10px 20px;text-decoration:none;
      border:1px solid #003366;border-radius:5px;font-weight:bold;
      transition:.3s;margin-left:10px;
    }
    .volver-btn:hover,.logout-btn:hover{background:#003366;color:#fff;}
    .search-container{
      background:#fff;padding:20px;border-radius:8px;
      box-shadow:0 2px 5px rgba(0,0,0,0.1);
      display:flex;gap:10px;flex-wrap:wrap;justify-content:center;
      margin:30px auto;max-width:95%;
    }
    .search-container input[list],
    .search-container input[type="date"],
    .search-container select{
      padding:8px;border:1px solid #aaa;border-radius:4px;min-width:150px;
    }
    .search-container button{
      padding:8px 16px;background:#003366;color:#fff;
      border:none;border-radius:4px;cursor:pointer;transition:.3s;
    }
    .search-container button:hover{background:#002244;}
    .container{
      background:#fff;padding:30px;margin:30px auto;width:95%;
      border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);
    }
    table{width:100%;border-collapse:collapse;margin-top:20px;}
    th,td{border:1px solid #ccc;padding:12px;text-align:left;vertical-align:top;white-space:normal;}
    th{background:#003366;color:#fff;}
    .status-revision{color:#d47e00;font-weight:bold;}
    .status-revisado{color:#007f0e;font-weight:bold;}
  </style>
</head>
<body>

  <header>
    <h1>Todos los Artículos</h1>
    <div>
      <a href="admin_home.php" class="volver-btn">Volver al inicio</a>
      <a href="logout.php" class="logout-btn">Cerrar sesión</a>
    </div>
  </header>

  <div class="search-container">
    <datalist id="titles">
      <?php while($t=$titlesRes->fetch_assoc()): ?>
        <option value="<?=htmlspecialchars($t['nombre_articulo'])?>">
      <?php endwhile; ?>
    </datalist>
    <datalist id="reviewers">
      <?php while($r=$reviewersRes->fetch_assoc()): ?>
        <option value="<?=htmlspecialchars($r['nombre_miembro'])?>">
      <?php endwhile; ?>
    </datalist>
    <datalist id="authors">
      <?php while($a=$authorsRes->fetch_assoc()): ?>
        <option value="<?=htmlspecialchars($a['nombre_miembro'])?>">
      <?php endwhile; ?>
    </datalist>

    <form method="GET" action="all_articulos.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <input list="titles"    name="title"    placeholder="Título…"  value="<?=htmlspecialchars($_GET['title']??'')?>">
      <select name="topic">
        <option value="">-- Tópico --</option>
        <?php while($tp=$topicsRes->fetch_assoc()): ?>
          <option value="<?=htmlspecialchars($tp['nombre_topico'])?>"
            <?=isset($_GET['topic']) && $_GET['topic']==$tp['nombre_topico']?'selected':''?>>
            <?=htmlspecialchars($tp['nombre_topico'])?>
          </option>
        <?php endwhile; ?>
      </select>
      <input type="date" name="date" value="<?=htmlspecialchars($_GET['date']??'')?>">
      <input list="reviewers" name="reviewer" placeholder="Revisor…" value="<?=htmlspecialchars($_GET['reviewer']??'')?>">
      <input list="authors"   name="author"   placeholder="Autor…"    value="<?=htmlspecialchars($_GET['author']??'')?>">
      <button type="submit">Filtrar</button>
      <button type="button" onclick="window.location='all_articulos.php'">Limpiar</button>
    </form>
  </div>

  <div class="container">
    <?php if($result && $result->num_rows): ?>
      <table>
        <thead>
          <tr>
            <th>Nombre Artículo</th>
            <th>Tópicos</th>
            <th>Fecha publicación</th>
            <th>Autores</th>
            <th>Revisores</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row=$result->fetch_assoc()): ?>
            <tr>
              <td><?=htmlspecialchars($row['nombre_articulo'])?></td>
              <td><?=htmlspecialchars($row['topicos']??'-')?></td>
              <td>
                <?= $row['fecha_de_publicacion']
                    ? date('d/m/Y', strtotime($row['fecha_de_publicacion']))
                    : '-' ?>
              </td>
              <td>
                <?= $row['autores']
                  ? implode('<br>', array_map('htmlspecialchars', explode(', ', $row['autores'])))
                  : '-' ?>
              </td>
              <td>
                <?= $row['revisores']
                  ? implode('<br>', array_map('htmlspecialchars', explode(', ', $row['revisores'])))
                  : '-' ?>
              </td>
              <td class="<?= $row['revisado'] ? 'status-revisado' : 'status-revision' ?>">
                <?= $row['revisado'] ? 'Revisado' : 'En revisión' ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No hay artículos registrados.</p>
    <?php endif; ?>
  </div>

</body>
</html>
