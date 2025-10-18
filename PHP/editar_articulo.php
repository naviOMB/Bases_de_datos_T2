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

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: todos_mis_articulos_autor.php');
    exit;
}
$id_art = (int)$_GET['id'];

$stmt = $mysqli->prepare("SELECT nombre_articulo, resumen_articulo FROM articulo WHERE id_articulo = ?");
$stmt->bind_param('i', $id_art);
$stmt->execute();
$stmt->bind_result($titulo_actual, $resumen_actual);
if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: todos_mis_articulos_autor.php');
    exit;
}
$stmt->close();

$top_selecc = [];
$res = $mysqli->query("SELECT id_topico FROM detalle_articulo WHERE id_articulo = $id_art");
while ($r = $res->fetch_assoc()) {
    $top_selecc[] = $r['id_topico'];
}
$res->free();

$aut_pub = [];
$stmt = $mysqli->prepare("
    SELECT dp.rut_miembro, m.nombre_miembro, m.email_miembro, dp.es_contacto
      FROM detalle_publicacion dp
      JOIN miembro m ON dp.rut_miembro = m.rut_miembro
     WHERE dp.id_articulo = ?
     ORDER BY dp.es_contacto DESC, m.nombre_miembro
");
$stmt->bind_param('i', $id_art);
$stmt->execute();
$stmt->bind_result($rut_a, $nom_a, $email_a, $es_contacto);
while ($stmt->fetch()) {
    $aut_pub[$rut_a] = [
        'nombre'   => $nom_a,
        'email'    => $email_a,
        'contacto' => (bool)$es_contacto
    ];
}
$stmt->close();

$num_autores = count($aut_pub);

$autoresRes = $mysqli->query("
  SELECT rut_miembro, nombre_miembro, email_miembro
    FROM miembro
   WHERE es_autor = 1 OR es_revisor = 1
   ORDER BY nombre_miembro
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete']) && $num_autores === 1) {
        $del1 = $mysqli->prepare("DELETE FROM detalle_articulo WHERE id_articulo = ?");
        $del1->bind_param('i', $id_art);
        $del1->execute();
        $del1->close();

        $del2 = $mysqli->prepare("DELETE FROM detalle_publicacion WHERE id_articulo = ?");
        $del2->bind_param('i', $id_art);
        $del2->execute();
        $del2->close();

        $del3 = $mysqli->prepare("DELETE FROM detalle_revision WHERE id_articulo = ?");
        $del3->bind_param('i', $id_art);
        $del3->execute();
        $del3->close();

        $del4 = $mysqli->prepare("DELETE FROM articulo WHERE id_articulo = ?");
        $del4->bind_param('i', $id_art);
        $del4->execute();
        $del4->close();

        header('Location: todos_mis_articulos_autor.php');
        exit;
    }

    $titulo         = trim($_POST['titulo']);
    $resumen        = trim($_POST['resumen']);
    $contacto_index = intval($_POST['contacto']);

    $upd = $mysqli->prepare("
      UPDATE articulo
         SET nombre_articulo = ?, resumen_articulo = ?
       WHERE id_articulo = ?
    ");
    $upd->bind_param('ssi', $titulo, $resumen, $id_art);
    $upd->execute();
    $upd->close();

    $nuevos_tops = isset($_POST['topicos']) && is_array($_POST['topicos'])
                  ? array_map('intval', $_POST['topicos'])
                  : [];

    $to_remove = array_diff($top_selecc, $nuevos_tops);
    if ($to_remove) {
        $del = $mysqli->prepare("
          DELETE FROM detalle_articulo
           WHERE id_articulo = ?
             AND id_topico   = ?
        ");
        foreach ($to_remove as $tid) {
            $del->bind_param('ii', $id_art, $tid);
            $del->execute();
        }
        $del->close();
    }

    $to_add = array_diff($nuevos_tops, $top_selecc);
    if ($to_add) {
        $ins = $mysqli->prepare("
          INSERT INTO detalle_articulo (id_articulo, id_topico)
          VALUES (?, ?)
        ");
        foreach ($to_add as $tid) {
            $ins->bind_param('ii', $id_art, $tid);
            $ins->execute();
        }
        $ins->close();
    }

    $ruts_post    = $_POST['rut_autor']   ?? [];
    $emails_post  = $_POST['email_autor'] ?? [];
    $names_post   = $_POST['nombre_autor']?? [];
    $new_pub = [];
    foreach ($ruts_post as $i => $rut) {
        $is_contact = ($i === $contacto_index) ? 1 : 0;
        $new_pub[$rut] = [
            'email'    => $emails_post[$i],
            'nombre'   => $names_post[$i],
            'contacto' => $is_contact,
        ];
    }
    $borrar = array_diff(array_keys($aut_pub), array_keys($new_pub));
    if ($borrar) {
        $del = $mysqli->prepare("
          DELETE FROM detalle_publicacion
           WHERE id_articulo  = ?
             AND rut_miembro = ?
        ");
        foreach ($borrar as $rut) {
            $del->bind_param('is', $id_art, $rut);
            $del->execute();
        }
        $del->close();
    }
    $updPub = $mysqli->prepare("
      UPDATE detalle_publicacion
         SET es_contacto = ?
       WHERE id_articulo  = ?
         AND rut_miembro = ?
    ");
    $insPub = $mysqli->prepare("
      INSERT INTO detalle_publicacion
        (rut_miembro, id_articulo, fecha_subida, es_contacto)
      VALUES (?, ?, CURDATE(), ?)
    ");
    foreach ($new_pub as $rut => $info) {
        if (isset($aut_pub[$rut])) {
            if ($aut_pub[$rut]['contacto'] !== (bool)$info['contacto']) {
                $updPub->bind_param('iis', $info['contacto'], $id_art, $rut);
                $updPub->execute();
            }
        } else {
            $insPub->bind_param('sis', $rut, $id_art, $info['contacto']);
            $insPub->execute();
        }
    }
    $updPub->close();
    $insPub->close();

    header("Location: articulo.php?id=$id_art");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Artículo</title>
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
    .eliminar{margin-left:10px;background:#cc0000;color:#fff;border:none;padding:10px 20px;border-radius:5px;cursor:pointer}
    .eliminar:hover{background:#990000}
  </style>
  <script>
    function agregarAutor(){
      const tbl = document.getElementById('tabla-autores');
      const base= tbl.rows[1];
      const nueva= base.cloneNode(true);
      nueva.querySelectorAll('input').forEach(i=>{
        if(i.type==='radio') i.checked=false;
        else i.value='';
      });
      tbl.appendChild(nueva);
      actualizarRadios();
    }
    function eliminarFila(btn){
      const tbl= document.getElementById('tabla-autores');
      if(tbl.rows.length>2){
        btn.closest('tr').remove();
        actualizarRadios();
      } else {
        alert('Debe haber al menos un autor.');
      }
    }
    function onNombreChange(input){
      const val= input.value.toLowerCase();
      const opts= document.querySelectorAll('#autoresList option');
      for(const o of opts){
        if(o.value.toLowerCase()===val){
          const tr= input.closest('tr');
          tr.querySelector('input[name="email_autor[]"]').value = o.dataset.email;
          tr.querySelector('input[name="rut_autor[]"]').value   = o.dataset.rut;
          break;
        }
      }
    }
    function actualizarRadios(){
      document.querySelectorAll('#tabla-autores tr').forEach((tr,i)=>{
        const radio= tr.querySelector('input[type="radio"]');
        if(radio) radio.value = i-1;
      });
    }
    window.addEventListener('load', actualizarRadios);
  </script>
</head>
<body>

<header>
  <h1>Editar Artículo</h1>
  <a class="volver" href="todos_mis_articulos_autor.php">Volver a mis artículos</a>
</header>

<form method="POST">
  <label for="titulo">Título del artículo:</label>
  <input type="text" id="titulo" name="titulo" required
         value="<?= htmlspecialchars($titulo_actual) ?>">

  <label for="resumen">Resumen:</label>
  <textarea id="resumen" name="resumen" rows="4" required><?= htmlspecialchars($resumen_actual) ?></textarea>

  <label>Autores (inclúyete también):</label>
  <table id="tabla-autores">
    <tr><th>Nombre</th><th>Email</th><th>Contacto</th><th>Eliminar</th></tr>
    <?php foreach ($aut_pub as $rut => $a): ?>
    <tr>
      <td>
        <input list="autoresList" name="nombre_autor[]" oninput="onNombreChange(this)" required
               value="<?= htmlspecialchars($a['nombre']) ?>">
        <datalist id="autoresList">
          <?php while($row = $autoresRes->fetch_assoc()): ?>
            <option data-rut="<?= htmlspecialchars($row['rut_miembro'])?>"
                    data-email="<?= htmlspecialchars($row['email_miembro'])?>"
                    value="<?= htmlspecialchars($row['nombre_miembro'])?>">
          <?php endwhile; ?>
        </datalist>
      </td>
      <td><input type="text" name="email_autor[]" required
                 value="<?= htmlspecialchars($a['email']) ?>"></td>
      <td class="centrado">
        <input type="radio" name="contacto" required <?= $a['contacto'] ? 'checked' : ''?>>
      </td>
      <td class="centrado">
        <button type="button" onclick="eliminarFila(this)">🗑️</button>
      </td>
      <input type="hidden" name="rut_autor[]" value="<?= htmlspecialchars($rut) ?>">
    </tr>
    <?php endforeach; ?>
  </table>
  <button type="button" class="boton-agregar" onclick="agregarAutor()">+ Agregar autor</button>

  <label>Tópicos del artículo:</label>
  <div class="topicos">
    <?php
      $tres = $mysqli->query("SELECT id_topico, nombre_topico FROM topico ORDER BY nombre_topico");
      while($t = $tres->fetch_assoc()){
        $sel = in_array($t['id_topico'], $top_selecc) ? 'checked' : '';
        echo '<label><input type="checkbox" name="topicos[]" value="'.$t['id_topico'].'" '.$sel.'> '
             .htmlspecialchars($t['nombre_topico'])."</label>\n";
      }
    ?>
  </div>

  <button type="submit" class="enviar">Guardar cambios</button>

  <?php if ($num_autores === 1): ?>
    <button type="submit" name="delete" value="1" class="eliminar"
            onclick="return confirm('¿Eliminar definitivamente este artículo?');">
      Eliminar artículo
    </button>
  <?php endif; ?>
</form>

</body>
</html>
