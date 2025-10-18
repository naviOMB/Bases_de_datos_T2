<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'autor') {
    header('Location: login.php');
    exit;
}

$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: '.$mysqli->connect_error);
}

$autoresRes = $mysqli->query("
  SELECT rut_miembro, nombre_miembro, email_miembro
    FROM miembro
   WHERE es_autor = 1 OR es_revisor = 1
   ORDER BY nombre_miembro
");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['contacto'])) {
        $error = 'Debes seleccionar un autor de contacto.';
    } elseif (empty($_POST['topicos']) || !is_array($_POST['topicos'])) {
        $error = 'Debes seleccionar al menos un tópico.';
    } else {
        $titulo    = trim($_POST['titulo']);
        $resumen   = trim($_POST['resumen']);
        $contacto  = intval($_POST['contacto']);

        $stmt = $mysqli->prepare("
          INSERT INTO articulo
            (nombre_articulo, resumen_articulo, fecha_publicacion, revisado)
          VALUES (?, ?, CURDATE(), 0)
        ");
        $stmt->bind_param('ss', $titulo, $resumen);
        $stmt->execute();
        $id_art = $stmt->insert_id;
        $stmt->close();

        $stmt = $mysqli->prepare("
          INSERT INTO detalle_articulo (id_articulo, id_topico)
          VALUES (?, ?)
        ");
        foreach ($_POST['topicos'] as $tid) {
            $stmt->bind_param('ii', $id_art, $tid);
            $stmt->execute();
        }
        $stmt->close();

        $nombres = $_POST['nombre_autor'];
        $emails  = $_POST['email_autor'];
        $ruts    = $_POST['rut_autor'];
        $stmt = $mysqli->prepare("
          INSERT INTO detalle_publicacion
            (rut_miembro, id_articulo, fecha_subida, es_contacto)
          VALUES (?, ?, CURDATE(), ?)
        ");
        foreach ($nombres as $i => $n) {
            $rut = $ruts[$i];
            $is_contact = ($i === $contacto) ? 1 : 0;
            $stmt->bind_param('sis', $rut, $id_art, $is_contact);
            $stmt->execute();
        }
        $stmt->close();

        $stmt = $mysqli->prepare("CALL asignar_revisores(?)");
        $stmt->bind_param('i', $id_art);
        $stmt->execute();
        $stmt->close();

        header('Location: todos_mis_articulos_autor.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Subir Artículo</title>
  <style>
    body { font-family: Arial,sans-serif; background:#f9f9f9; margin:0 }
    header{background:#003366;color:#fff;display:flex;justify-content:space-between;align-items:center;padding:15px 30px}
    header h1{margin:0}
    .volver{background:#fff;color:#003366;padding:8px 16px;border:1px solid #003366;border-radius:5px;text-decoration:none;font-weight:bold}
    form{margin:30px;background:#fff;padding:20px;border-radius:10px;box-shadow:0 0 10px #ccc}
    label{font-weight:bold;display:block;margin-top:15px}
    input[type="text"],textarea{width:100%;padding:10px;margin-top:5px;border:1px solid #aaa;border-radius:5px;box-sizing:border-box}
    table{width:100%;border-collapse:collapse;margin-top:15px}
    th,td{border:1px solid #ccc;padding:10px;vertical-align:middle}
    .centrado{text-align:center}
    .boton-agregar{margin-top:10px;padding:6px 12px;font-weight:bold}
    .topicos label{display:block;margin-top:5px}
    .enviar{margin-top:20px;background:#003366;color:#fff;border:none;padding:10px 25px;border-radius:5px;cursor:pointer}
    .enviar:hover{background:#002244}
    .error{color:red;margin-top:10px;font-weight:bold;}
  </style>
  <script>
    function agregarAutor() {
      const tbl = document.getElementById('tabla-autores');
      const base = tbl.rows[1];
      const nueva = base.cloneNode(true);
      nueva.querySelectorAll('input').forEach(i => {
        if (i.type === 'radio') i.checked = false;
        else i.value = '';
      });
      tbl.appendChild(nueva);
      actualizarRadios();
    }
    function eliminarFila(btn) {
      const tbl = document.getElementById('tabla-autores');
      if (tbl.rows.length > 2) {
        btn.closest('tr').remove();
        actualizarRadios();
      } else {
        alert('Debe haber al menos un autor.');
      }
    }
    function onNombreChange(input) {
      const val = input.value.toLowerCase();
      const opts = document.querySelectorAll('#autoresList option');
      for (const o of opts) {
        if (o.value.toLowerCase() === val) {
          const tr = input.closest('tr');
          tr.querySelector('input[name="email_autor[]"]').value = o.dataset.email;
          tr.querySelector('input[name="rut_autor[]"]').value   = o.dataset.rut;
          break;
        }
      }
    }
    function actualizarRadios() {
      document.querySelectorAll('#tabla-autores tr').forEach((tr, i) => {
        const radio = tr.querySelector('input[type="radio"]');
        if (radio) radio.value = i - 1;
      });
    }
    function validateForm() {
      if (!document.querySelector('input[name="contacto"]:checked')) {
        alert('Debes seleccionar un autor de contacto.');
        return false;
      }
      if (!document.querySelector('input[name="topicos[]"]:checked')) {
        alert('Debes seleccionar al menos un tópico.');
        return false;
      }
      return true;
    }
    window.addEventListener('load', actualizarRadios);
  </script>
</head>
<body>

<header>
  <h1>Subir Artículo</h1>
  <a class="volver" href="autor_home.php">Volver a Home</a>
</header>

<form method="POST" onsubmit="return validateForm()">
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <label for="titulo">Título del artículo:</label>
  <input type="text" id="titulo" name="titulo" placeholder="Escribe el título..." required
         value="<?= isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : '' ?>">

  <label for="resumen">Resumen:</label>
  <textarea id="resumen" name="resumen" rows="4" placeholder="Escribe el resumen..." required><?= isset($_POST['resumen']) ? htmlspecialchars($_POST['resumen']) : '' ?></textarea>

  <label>Autores (inclúyete también):</label>
  <table id="tabla-autores">
    <tr><th>Nombre</th><th>Email</th><th>Contacto</th><th>Eliminar</th></tr>
    <tr>
      <td>
        <input list="autoresList" name="nombre_autor[]" oninput="onNombreChange(this)" required>
        <datalist id="autoresList">
          <?php while($a=$autoresRes->fetch_assoc()): ?>
            <option
              data-rut="<?= htmlspecialchars($a['rut_miembro'])?>"
              data-email="<?= htmlspecialchars($a['email_miembro'])?>"
              value="<?= htmlspecialchars($a['nombre_miembro'])?>">
          <?php endwhile; ?>
        </datalist>
      </td>
      <td><input type="text" name="email_autor[]" required></td>
      <td class="centrado"><input type="radio" name="contacto" required></td>
      <td class="centrado"><button type="button" onclick="eliminarFila(this)">🗑️</button></td>
      <input type="hidden" name="rut_autor[]">
    </tr>
  </table>
  <button type="button" class="boton-agregar" onclick="agregarAutor()">+ Agregar autor</button>

  <label>Tópicos del artículo:</label>
  <div class="topicos">
    <?php
      $tres = $mysqli->query("SELECT id_topico,nombre_topico FROM topico ORDER BY nombre_topico");
      while($t=$tres->fetch_assoc()){
        echo '<label><input type="checkbox" name="topicos[]" value="'.$t['id_topico'].'"> '
             .htmlspecialchars($t['nombre_topico'])."</label>\n";
      }
    ?>
  </div>

  <button type="submit" class="enviar">Enviar</button>
</form>

</body>
</html>
