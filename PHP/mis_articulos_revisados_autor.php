<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'autor') {
    header('Location: login.php');
    exit;
}

$mysqli = new mysqli('localhost', 'root', '', 'test');
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$sql = "
  SELECT
    a.id_articulo,
    a.nombre_articulo,
    a.resumen_articulo,
    a.revisado          AS Estado,
    GROUP_CONCAT(DISTINCT t.nombre_topico SEPARATOR ', ') AS topicos,
    (
      SELECT GROUP_CONCAT(m2.nombre_miembro SEPARATOR ', ')
        FROM detalle_publicacion dp2
        JOIN miembro m2 ON dp2.rut_miembro = m2.rut_miembro
       WHERE dp2.id_articulo = a.id_articulo
    ) AS autores,
    (
      SELECT GROUP_CONCAT(m3.nombre_miembro SEPARATOR ', ')
        FROM detalle_revision dr3
        JOIN miembro m3 ON dr3.rut_miembro = m3.rut_miembro
       WHERE dr3.id_articulo = a.id_articulo
    ) AS revisores
  FROM articulo a
  JOIN detalle_publicacion dp
    ON a.id_articulo = dp.id_articulo
  JOIN miembro m
    ON dp.rut_miembro = m.rut_miembro
   AND m.userid_miembro = ?
  JOIN detalle_articulo da
    ON a.id_articulo = da.id_articulo
  JOIN topico t
    ON da.id_topico = t.id_topico
 WHERE a.revisado = 1
 GROUP BY a.id_articulo
 ORDER BY a.id_articulo DESC
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $_SESSION['usuario']);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis artículos revisados – GESCON</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f9f9f9; margin:0; padding:0 }
    header { background:#003366; color:#fff; padding:15px 30px;
             display:flex; justify-content:space-between; align-items:center }
    header h1 { margin:0 }
    .volver {
      text-decoration:none; background:#fff; color:#003366;
      border:1px solid #003366; padding:8px 16px; border-radius:5px;
      font-weight:bold;
    }
    .container { padding:30px }
    .top-bar { display:flex; justify-content:space-between; align-items:center }
    .busqueda input {
      padding:8px; width:200px; border:1px solid #ccc; border-radius:4px
    }

    table { width:100%; border-collapse:collapse; margin-top:10px }
    th, td { border:1px solid #ccc; padding:10px; vertical-align:top }
    th { background:#003366; color:#fff; text-align:left }

    td.resumen {
      max-width: 250px;
      white-space: normal;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }

    td.revisores {
      white-space: normal;
      line-height: 1.4em;
    }

    .link-articulo {
      color:#003366; font-weight:bold; text-decoration:none;
    }
    .link-articulo:hover { text-decoration:underline }

    .estado-revisado { color:green;  font-weight:bold }
    .estado-enviado  { color:orange; font-weight:bold }

  </style>
</head>
<body>

  <header>
    <h1>Mis artículos revisados</h1>
    <a class="volver" href="autor_home.php">Volver a Home</a>
  </header>

  <div class="container">
    <div class="top-bar">
      <div class="busqueda">
        <input
          type="text"
          placeholder="Buscar en cualquier columna…"
          onkeyup="filtrar(this)">
      </div>
    </div>

    <table id="tbl">
      <thead>
        <tr>
          <th>Título</th>
          <th>Resumen</th>
          <th>Tópicos</th>
          <th>Autores</th>
          <th>Revisores</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td>
                <a
                  href="articulo.php?id=<?= $row['id_articulo'] ?>"
                  class="link-articulo">
                  <?= htmlspecialchars($row['nombre_articulo']) ?>
                </a>
              </td>
              <td class="resumen">
                <?= htmlspecialchars($row['resumen_articulo']) ?>
              </td>
              <td>
                <?= htmlspecialchars($row['topicos']) ?>
              </td>
              <td>
                <?php
                  echo implode(
                    '<br>',
                    array_map('htmlspecialchars', explode(', ', $row['autores'] ?? ''))
                  ) ?: '-';
                ?>
              </td>
              <td class="revisores">
                <?php
                  echo implode(
                    '<br>',
                    array_map('htmlspecialchars', explode(', ', $row['revisores'] ?? ''))
                  ) ?: '-';
                ?>
              </td>
              <td class="<?= $row['Estado'] ? 'estado-revisado' : 'estado-enviado' ?>">
                <?= $row['Estado'] ? 'Revisado' : 'Enviado' ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align:center">
              No tienes artículos revisados aún.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <script>
    function filtrar(input) {
      const q = input.value.toLowerCase();
      document.querySelectorAll('#tbl tbody tr').forEach(row => {
        const textoFila = Array.from(row.cells)
          .map(td => td.innerText.toLowerCase())
          .join(' ');
        row.style.display = textoFila.includes(q) ? '' : 'none';
      });
    }
  </script>

</body>
</html>
