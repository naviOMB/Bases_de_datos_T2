<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Conexión
$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: '.$mysqli->connect_error);
}

// Carga datos de usuario
$stmt = $mysqli->prepare("SELECT * FROM miembro WHERE userid_miembro = ?");
$stmt->bind_param('s', $_SESSION['usuario']);
$stmt->execute();
$usuarioData = $stmt->get_result()->fetch_assoc();
$stmt->close();

$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['do_delete'])) {
    $rut = $usuarioData['rut_miembro'];

    // — ELIMINAR ROL DE AUTOR —
    if (!empty($_POST['del_autor']) && $usuarioData['es_autor']) {
        // 1) Identificar artículos de autor único
        $q = $mysqli->prepare("SELECT id_articulo FROM detalle_publicacion WHERE rut_miembro=?");
        $q->bind_param('s',$rut);
        $q->execute();
        $solo = array_column($q->get_result()->fetch_all(MYSQLI_NUM),0);
        $q->close();
        foreach($solo as $id) {
            $c = $mysqli->prepare("SELECT COUNT(*) FROM detalle_publicacion WHERE id_articulo=?");
            $c->bind_param('i',$id);
            $c->execute();
            $cnt = $c->get_result()->fetch_row()[0];
            $c->close();
            if ($cnt===1) {
                // limpiar tablas hijas antes de borrar el artículo
                $stmt = $mysqli->prepare("DELETE FROM detalle_articulo   WHERE id_articulo=?");
                $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
                $stmt = $mysqli->prepare("DELETE FROM detalle_revision  WHERE id_articulo=?");
                $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
                $stmt = $mysqli->prepare("DELETE FROM detalle_publicacion WHERE id_articulo=?");
                $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
                // finalmente elimina el artículo
                $mysqli->query("DELETE FROM articulo WHERE id_articulo=$id");
            }
        }
        // 2) Borrar todas sus publicaciones restantes
        $d = $mysqli->prepare("DELETE FROM detalle_publicacion WHERE rut_miembro=?");
        $d->bind_param('s',$rut); $d->execute(); $d->close();
        // 3) Desactivar autor
        $mysqli->query("UPDATE miembro SET es_autor=0 WHERE rut_miembro='$rut'");
    }

    // — ELIMINAR ROL DE REVISOR —
    if (!empty($_POST['del_revisor']) && $usuarioData['es_revisor']) {
        $q = $mysqli->prepare(
          "SELECT COUNT(*) FROM detalle_revision
           WHERE rut_miembro=? AND fecha_revision IS NULL"
        );
        $q->bind_param('s',$rut);
        $q->execute();
        $pend = $q->get_result()->fetch_row()[0];
        $q->close();
        if ($pend>0) {
            $errors[] = "No puedes eliminar tu rol de revisor: tienes $pend revisiones pendientes.";
        } else {
            // borrar revisiones y tópicos
            $d1 = $mysqli->prepare("DELETE FROM detalle_revision WHERE rut_miembro=?");
            $d1->bind_param('s',$rut); $d1->execute(); $d1->close();
            $d2 = $mysqli->prepare("DELETE FROM detalle_topico WHERE rut_miembro=?");
            $d2->bind_param('s',$rut); $d2->execute(); $d2->close();
            $mysqli->query("UPDATE miembro SET es_revisor=0 WHERE rut_miembro='$rut'");
        }
    }

    // — SI YA NO ES AUTOR, NI REVISOR, NI ADMIN → BORRAR USUARIO COMPLETO —
    if (empty($errors)) {
        $r2 = $mysqli->prepare("SELECT es_autor,es_revisor,es_admin FROM miembro WHERE rut_miembro=?");
        $r2->bind_param('s',$rut); $r2->execute();
        $f = $r2->get_result()->fetch_assoc(); $r2->close();
        if (!$f['es_autor'] && !$f['es_revisor'] && !$f['es_admin']) {
            $mysqli->query("DELETE FROM miembro WHERE rut_miembro='$rut'");
            session_destroy();
            header('Location: goodbye.php');
            exit;
        }
        header('Location: cuenta.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Cuenta – GESCON</title>
  <style>
    body { font-family:Arial,sans-serif; background:#f4f4f4; margin:0; }
    header { background:#003366; color:#fff; padding:15px 30px;
      display:flex; justify-content:space-between; align-items:center; }
    .btn { background:#fff; color:#003366; padding:8px 16px;
      text-decoration:none; border:1px solid #003366; border-radius:4px;
      margin-left:10px; transition:.2s; }
    .btn:hover { background:#002244; color:#fff; }
    .container { background:#fff; max-width:700px; margin:30px auto;
      padding:30px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.1); }
    label { display:block; margin-top:15px; font-weight:bold; }
    input, select { width:100%; padding:10px; margin-top:5px;
      border:1px solid #ccc; border-radius:4px; }
    .guardar-btn { margin-top:20px; background:#003366; color:#fff;
      border:none; padding:10px 20px; border-radius:4px; cursor:pointer;
      transition:.2s; }
    .guardar-btn:hover { background:#002244; }
    fieldset { margin-top:30px; padding:20px; border:1px solid #ddd;
      border-radius:6px; background:#fafafa; }
    fieldset legend { font-weight:bold; }
    .roles-list {
      display: flex;
      gap: 2em;
      margin-top: 10px;
    }
    .roles-list label {
      display: inline-flex;
      align-items: center;
      font-weight: normal;
    }
    .roles-list input[type="checkbox"] {
      margin-left: 0.5em;
      transform: scale(1.2);
    }
    .submit-delete { margin-top:15px; background:#c0392b; color:#fff;
      border:none; padding:8px 16px; border-radius:4px; cursor:pointer;
      transition:.2s; }
    .submit-delete:hover { background:#992d22; }
    .error { background:#fdd; color:#900; padding:10px; margin-bottom:15px;
      border:1px solid #d33; border-radius:4px; }
  </style>
</head>
<body>

<header>
  <h1>Mi Cuenta</h1>
  <?php
    $homes = [
      'autor'   => 'autor_home.php',
      'revisor' => 'revisor_home.php',
      'admin'   => 'admin_home.php'
    ];
    $home = $homes[$_SESSION['rol']] ?? 'login.php';
  ?>
  <div>
    <a href="<?=htmlspecialchars($home)?>" class="btn">Volver</a>
    <a href="logout.php" class="btn">Cerrar sesión</a>
  </div>
</header>

<div class="container">
    <h2>Información de la cuenta</h2>
    <form action="procesar_actualizacion_cuenta.php" method="POST">
      <label for="userid">Usuario:</label>
      <input type="text" id="userid" value="<?=htmlspecialchars($usuarioData['userid_miembro'])?>" disabled>

      <label for="nombre">Nombre:</label>
      <input type="text" id="nombre" name="nombre"
             value="<?=htmlspecialchars($usuarioData['nombre_miembro'])?>" required>

      <label for="password">Contraseña:</label>
      <input type="password" id="password" name="password"
             value="<?=htmlspecialchars($usuarioData['password_miembro'])?>" required>

      <label for="email">Correo electrónico:</label>
      <input type="email" id="email" name="email"
             value="<?=htmlspecialchars($usuarioData['email_miembro'])?>" required>

      <label for="rol">Modo actual:</label>
      <select id="rol" name="rol" required>
        <?php if ($usuarioData['es_autor']): ?>
          <option value="autor" <?= $_SESSION['rol']==='autor'   ? 'selected' : '' ?>>Autor</option>
        <?php endif; ?>
        <?php if ($usuarioData['es_revisor']): ?>
          <option value="revisor"<?= $_SESSION['rol']==='revisor' ? 'selected' : '' ?>>Revisor</option>
        <?php endif; ?>
        <?php if ($usuarioData['es_admin']): ?>
          <option value="admin" <?= $_SESSION['rol']==='admin'    ? 'selected' : '' ?>>Admin</option>
        <?php endif; ?>
      </select>

      <button type="submit" class="guardar-btn">Guardar Cambios</button>
    </form>

  <fieldset>
    <legend>Eliminar Roles</legend>
    <form method="POST" onsubmit="return confirm('¿Seguro?');">
      <div class="roles-list">
        <?php if($usuarioData['es_autor']): ?>
          <label>
            Eliminar rol de Autor
            <input type="checkbox" name="del_autor">
          </label>
        <?php endif; ?>
        <?php if($usuarioData['es_revisor']): ?>
          <label>
            Eliminar rol de Revisor
            <input type="checkbox" name="del_revisor">
          </label>
        <?php endif; ?>
      </div>
      <button type="submit" name="do_delete" class="submit-delete">Aplicar Eliminaciones</button>
    </form>
  </fieldset>
</div>
</body>
</html>
