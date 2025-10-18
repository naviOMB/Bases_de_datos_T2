<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && !empty($_POST['rut'])) {
        $rut = $_POST['rut'];

        $stmt = $mysqli->prepare("
            UPDATE miembro
               SET es_revisor = 0,
                   es_autor   = 1
             WHERE rut_miembro = ?
        ");
        $stmt->bind_param('s', $rut);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("
            DELETE FROM detalle_revision
             WHERE rut_miembro = ?
        ");
        $stmt->bind_param('s', $rut);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("
            DELETE FROM detalle_topico
             WHERE rut_miembro = ?
        ");
        $stmt->bind_param('s', $rut);
        $stmt->execute();
        $stmt->close();
    }

    if ($action === 'update_topics' && !empty($_POST['rut'])) {
        $rut = $_POST['rut'];

        $stmt = $mysqli->prepare("DELETE FROM detalle_topico WHERE rut_miembro = ?");
        $stmt->bind_param('s', $rut);
        $stmt->execute();
        $stmt->close();

        if (!empty($_POST['topics']) && is_array($_POST['topics'])) {
            $stmt = $mysqli->prepare("INSERT INTO detalle_topico (rut_miembro, id_topico) VALUES (?, ?)");
            foreach ($_POST['topics'] as $top) {
                $stmt->bind_param('si', $rut, $top);
                $stmt->execute();
            }
            $stmt->close();
        }
    }

    if ($action === 'add' && !empty($_POST['new_rut'])) {
        $rut = $_POST['new_rut'];

        $stmt = $mysqli->prepare("UPDATE miembro SET es_revisor = 1 WHERE rut_miembro = ?");
        $stmt->bind_param('s', $rut);
        $stmt->execute();
        $stmt->close();

        if (!empty($_POST['topics_add']) && is_array($_POST['topics_add'])) {
            $stmt = $mysqli->prepare("INSERT INTO detalle_topico (rut_miembro, id_topico) VALUES (?, ?)");
            foreach ($_POST['topics_add'] as $top) {
                $stmt->bind_param('si', $rut, $top);
                $stmt->execute();
            }
            $stmt->close();
        }
    }

    header('Location: gestion_revisiones.php');
    exit;
}

$resRevisores = $mysqli->query("
  SELECT rut_miembro, nombre_miembro, email_miembro
    FROM miembro
   WHERE es_revisor = 1
   ORDER BY nombre_miembro
");

$resNo = $mysqli->query("
  SELECT rut_miembro, nombre_miembro
    FROM miembro
   WHERE es_revisor = 0
   ORDER BY nombre_miembro
");

$resTopics = $mysqli->query("SELECT id_topico, nombre_topico FROM topico ORDER BY nombre_topico");
$topics = [];
while ($t = $resTopics->fetch_assoc()) {
    $topics[] = $t;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestionar Revisores – GESCON</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; }
    header { background:#003366; color:white; display:flex; justify-content:space-between; align-items:center; padding:15px 30px; }
    header h1 { margin:0; }
    .volver-btn, .logout-btn {
      background:#fff; color:#003366; padding:10px 20px;
      text-decoration:none; border:1px solid #003366; border-radius:5px;
      font-weight:bold; transition:.3s; margin-left:10px;
    }
    .volver-btn:hover, .logout-btn:hover { background:#003366; color:#fff; }
    .container {
      background:#fff; padding:30px; margin:30px auto; max-width:900px;
      border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1);
    }
    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #ccc; padding:12px; text-align:left; vertical-align:top; }
    th { background:#003366; color:#fff; }
    .btn-del {
      background:#c0392b; color:white; padding:6px 12px;
      border:none; border-radius:4px; cursor:pointer;
    }
    .btn-save {
      background:#27ae60; color:white; padding:6px 12px;
      border:none; border-radius:4px; cursor:pointer;
    }
    .btn-add, .btn-new {
      display:inline-block; vertical-align:middle;
      background:#2980b9; color:white; padding:10px 20px;
      border:none; border-radius:4px; cursor:pointer;
      margin-top:0; margin-bottom:0;
    }
    .btn-new {
      padding:8px 16px; font-size:0.9em; margin-left:10px;
    }
    fieldset { border:1px solid #aaa; padding:10px; margin-top:10px; }
    legend { font-weight:bold; }
    .form-inline { display:inline-block; vertical-align:middle; }
  </style>
  <script>
    function validateNewReviewerForm() {
      const checkboxes = document.querySelectorAll('input[name="topics_add[]"]:checked');
      if (checkboxes.length === 0) {
        alert('Por favor selecciona al menos una especialidad para el nuevo revisor.');
        return false;
      }
      return true;
    }
  </script>
</head>
<body>

<header>
  <h1>Gestionar Revisores</h1>
  <div>
    <a href="admin_home.php" class="volver-btn">Volver al inicio</a>
    <a href="logout.php" class="logout-btn">Cerrar sesión</a>
  </div>
</header>

<div class="container">
  <table>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Especialidades</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($r = $resRevisores->fetch_assoc()): ?>
        <?php
          $stmt = $mysqli->prepare("SELECT id_topico FROM detalle_topico WHERE rut_miembro = ?");
          $stmt->bind_param('s', $r['rut_miembro']);
          $stmt->execute();
          $resT = $stmt->get_result();
          $mine = [];
          while ($rowT = $resT->fetch_assoc()) {
              $mine[] = $rowT['id_topico'];
          }
          $stmt->close();
        ?>
        <tr>
          <td><?= htmlspecialchars($r['nombre_miembro']) ?></td>
          <td><?= htmlspecialchars($r['email_miembro']) ?></td>
          <td>
            <form method="POST" class="form-inline">
              <fieldset>
                <legend>Tópicos</legend>
                <?php foreach ($topics as $t): ?>
                  <label>
                    <input type="checkbox"
                           name="topics[]"
                           value="<?= $t['id_topico'] ?>"
                           <?= in_array($t['id_topico'], $mine) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($t['nombre_topico']) ?>
                  </label><br>
                <?php endforeach; ?>
              </fieldset>
              <input type="hidden" name="action" value="update_topics">
              <input type="hidden" name="rut"    value="<?= $r['rut_miembro'] ?>">
              <button type="submit" class="btn-save">Guardar</button>
            </form>
          </td>
          <td>
            <form method="POST"
                  onsubmit="return confirm('¿Estás seguro de eliminar este revisor y sus datos?');"
                  class="form-inline">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="rut"    value="<?= $r['rut_miembro'] ?>">
              <button type="submit" class="btn-del">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <hr>

  <h2>Agregar Revisor</h2>
  <form method="POST" onsubmit="return validateNewReviewerForm();">
    <input type="hidden" name="action" value="add">
    <label for="new_rut">Usuario:</label>
    <select name="new_rut" id="new_rut" required
            style="width:200px; display:inline-block; margin-right:10px;">
      <option value="">-- Seleccionar usuario --</option>
      <?php while ($u = $resNo->fetch_assoc()): ?>
        <option value="<?= $u['rut_miembro'] ?>">
          <?= htmlspecialchars($u['nombre_miembro']) ?>
        </option>
      <?php endwhile; ?>
    </select>
    <a href="nuevo_revisor.php" class="btn-new">Nuevo Usuario</a>

    <fieldset>
      <legend>Especialidades</legend>
      <?php foreach ($topics as $t): ?>
        <label>
          <input type="checkbox"
                 name="topics_add[]"
                 value="<?= $t['id_topico'] ?>">
          <?= htmlspecialchars($t['nombre_topico']) ?>
        </label><br>
      <?php endforeach; ?>
    </fieldset>

    <button type="submit" class="btn-add">Agregar Revisor</button>
  </form>
</div>

</body>
</html>
