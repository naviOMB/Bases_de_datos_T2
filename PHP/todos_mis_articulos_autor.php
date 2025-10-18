<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'autor') {
    header('Location: login.php');
    exit;
}

$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

$userid = $_SESSION['usuario'];
$stmt = $mysqli->prepare("SELECT nombre_miembro FROM miembro WHERE userid_miembro = ?");
$stmt->bind_param('s', $userid);
$stmt->execute();
$stmt->bind_result($nombre_autor);
$stmt->fetch();
$stmt->close();

$search = '';
if (!empty($_GET['search'])) {
    $search = trim($_GET['search']);
}

$stmt = $mysqli->prepare("CALL obtener_articulos_all_autor(?)");
$stmt->bind_param('s', $nombre_autor);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Todos mis artículos – GESCON</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 0; }
    header { background: #003366; color: #fff; padding: 15px 30px;
             display: flex; justify-content: space-between; align-items: center; }
    header h1 { margin: 0; }
    .volver { background: #fff; color: #003366; border: 1px solid #003366;
              padding: 8px 16px; border-radius: 5px; text-decoration: none; font-weight: bold; }
    .container { padding: 30px; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .top-bar .busqueda {
      display: flex;
      border: 1px solid #ccc;
      border-radius: 4px;
      overflow: hidden;
      background: #fff;
    }
    .top-bar .busqueda input[type="text"] {
      border: none;
      padding: 8px 12px;
      font-size: 1em;
      width: 200px;
      outline: none;
    }
    .top-bar .busqueda button {
      background: #003366;
      color: #fff;
      border: none;
      padding: 0 16px;
      cursor: pointer;
      transition: background .2s;
    }
    .top-bar .busqueda button:hover { background: #002244; }

    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    colgroup col.name        { width: 20%; }
    colgroup col.description { width: 35%; }
    th, td { border: 1px solid #ccc; padding: 10px; vertical-align: top; }
    th { background: #003366; color: #fff; text-align: left; }
    td.description {
      white-space: normal;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }
    td.authors, td.reviewers {
      white-space: normal;
      line-height: 1.4em;
    }
    .link-articulo { color: #003366; font-weight: bold; text-decoration: none; }
    .link-articulo:hover { text-decoration: underline; }
    .estado-revisado { color: green;  font-weight: bold; }
    .estado-enviado  { color: orange; font-weight: bold; }
  </style>
</head>
<body>

<header>
  <h1>Todos mis artículos</h1>
  <a class="volver" href="autor_home.php">Volver a Home</a>
</header>

<div class="container">

  <div class="top-bar">
    <div></div>
    <form method="GET" action="todos_mis_articulos_autor.php" class="busqueda">
      <input
        type="text"
        name="search"
        placeholder="Buscar artículo..."
        value="<?= htmlspecialchars($search) ?>"
      >
      <button type="submit">Buscar</button>
    </form>
  </div>

  <table>
    <colgroup>
      <col class="name">
      <col class="description">
      <col>
      <col>
      <col>
      <col>
    </colgroup>
    <thead>
      <tr>
        <th>Nombre artículo</th>
        <th>Descripción</th>
        <th>Fecha publicación</th>
        <th>Autores</th>
        <th>Revisores</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $found = false;
        if ($result && $result->num_rows) {
          while ($row = $result->fetch_assoc()) {
            if ($search !== '' &&
                stripos($row['nombre_articulo'], $search) === false) {
              continue;
            }
            $found = true;
      ?>
      <tr>
        <td>
          <?php if (!empty($row['id_articulo'])): ?>
            <a href="articulo.php?id=<?= urlencode($row['id_articulo']) ?>"
               class="link-articulo">
              <?= htmlspecialchars($row['nombre_articulo']) ?>
            </a>
          <?php else: ?>
            <?= htmlspecialchars($row['nombre_articulo']) ?>
          <?php endif; ?>
        </td>
        <td class="description">
          <?= htmlspecialchars($row['descripcion'] ?? '-') ?>
        </td>
        <td>
          <?= htmlspecialchars($row['Fecha_de_publicacion'] ?? '-') ?>
        </td>
        <td class="authors">
          <?php
            if (!empty($row['Autores'])) {
              echo implode(
                '<br>',
                array_map('htmlspecialchars', explode(', ', $row['Autores']))
              );
            } else {
              echo '-';
            }
          ?>
        </td>
        <td class="reviewers">
          <?php
            if (!empty($row['Revisores'])) {
              echo implode(
                '<br>',
                array_map('htmlspecialchars', explode(', ', $row['Revisores']))
              );
            } else {
              echo '-';
            }
          ?>
        </td>
        <td class="<?= $row['Estado'] ? 'estado-revisado' : 'estado-enviado' ?>">
          <?= $row['Estado'] ? 'Revisado' : 'Enviado' ?>
        </td>
      </tr>
      <?php
          }
        }
        if (!$found) {
      ?>
      <tr>
        <td colspan="6" style="text-align:center;">
          <?= $search === ''
              ? 'No has publicado artículos todavía.'
              : 'No se encontraron artículos que coincidan con “'.htmlspecialchars($search).'”.' 
          ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

</div>

</body>
</html>
