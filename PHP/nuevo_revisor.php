<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$errors = ['rut'=>'', 'nombre'=>'', 'email'=>'', 'userid'=>''];
$values = ['rut'=>'', 'nombre'=>'', 'email'=>'', 'userid'=>'', 'password'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize
    foreach ($values as $field => &$v) {
        $v = trim($_POST[$field] ?? '');
    }
    unset($v);

    if ($values['rut']==='')    $errors['rut']    = 'RUT requerido';
    if ($values['nombre']==='') $errors['nombre'] = 'Nombre requerido';
    if ($values['email']==='')  $errors['email']  = 'Email requerido';
    if ($values['userid']==='') $errors['userid'] = 'Usuario requerido';
    if ($values['password']==='') {
        $pw_error = 'Contraseña requerida';
    }

    $mysqli = new mysqli('localhost','root','','test');
    if ($mysqli->connect_error) {
        die('Error de conexión: '.$mysqli->connect_error);
    }

    if (!array_filter($errors) && empty($pw_error)) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM miembro WHERE rut_miembro=?");
        $stmt->bind_param('s',$values['rut']);
        $stmt->execute();
        $stmt->bind_result($cnt);
        $stmt->fetch();
        $stmt->close();
        if ($cnt>0) $errors['rut']='Ese RUT ya existe';

        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM miembro WHERE nombre_miembro=?");
        $stmt->bind_param('s',$values['nombre']);
        $stmt->execute();
        $stmt->bind_result($cnt);
        $stmt->fetch();
        $stmt->close();
        if ($cnt>0) $errors['nombre']='Ese nombre ya existe';

        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM miembro WHERE email_miembro=?");
        $stmt->bind_param('s',$values['email']);
        $stmt->execute();
        $stmt->bind_result($cnt);
        $stmt->fetch();
        $stmt->close();
        if ($cnt>0) $errors['email']='Ese email ya existe';

        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM miembro WHERE userid_miembro=?");
        $stmt->bind_param('s',$values['userid']);
        $stmt->execute();
        $stmt->bind_result($cnt);
        $stmt->fetch();
        $stmt->close();
        if ($cnt>0) $errors['userid']='Ese usuario ya existe';
    }

    if (!array_filter($errors) && empty($pw_error)) {
        $stmt = $mysqli->prepare("
            INSERT INTO miembro
              (rut_miembro,nombre_miembro,email_miembro,userid_miembro,password_miembro,
               es_autor,es_revisor,es_admin)
            VALUES(?,?,?,?,?,0,1,0)
        ");
        $stmt->bind_param('sssss',
            $values['rut'],
            $values['nombre'],
            $values['email'],
            $values['userid'],
            $values['password']
        );
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
        echo <<<HTML
        <!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Ok</title>
        <script>
          alert('Nuevo usuario revisor creado. Se ha enviado una notificación al correo electrónico con sus credenciales.');
          window.location='gestion_revisiones.php';
        </script>
        </head><body></body></html>
        HTML;
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Revisor – GESCON</title>
  <style>
    body{margin:0;padding:0;background:url('../img/fondo_login.jpg')no-repeat center fixed; background-size:cover;font-family:Arial;}
    .registro-container{background:rgba(255,255,255,0.9);max-width:400px;margin:80px auto;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.3);}
    h1{text-align:center;color:#003366;margin-bottom:20px;}
    label{display:block;margin-top:15px;font-weight:bold;color:#333;}
    input{width:100%;padding:10px;margin-top:5px;border:1px solid #aaa;border-radius:5px;font-size:1em;}
    .error{color:#c0392b;font-size:.9em;margin-top:3px;}
    button{margin-top:25px;width:100%;padding:12px;background:#003366;color:#fff;border:none;border-radius:5px;cursor:pointer;transition:.3s;}
    button:hover{background:#002244;}
    .volver{text-align:center;margin-top:15px;}
    .volver a{color:#003366;text-decoration:none;font-size:.9em;}
    .volver a:hover{text-decoration:underline;}
  </style>
</head>
<body>
  <div class="registro-container">
    <h1>Nuevo Revisor</h1>
    <form method="POST" action="">
      <label for="rut">RUT</label>
      <input id="rut" name="rut" value="<?= htmlspecialchars($values['rut'])?>" required>
      <?php if($errors['rut']): ?><div class="error"><?= $errors['rut'] ?></div><?php endif; ?>

      <label for="nombre">Nombre</label>
      <input id="nombre" name="nombre" value="<?= htmlspecialchars($values['nombre'])?>" required>
      <?php if($errors['nombre']): ?><div class="error"><?= $errors['nombre'] ?></div><?php endif; ?>

      <label for="email">Email</label>
      <input id="email" type="email" name="email" value="<?= htmlspecialchars($values['email'])?>" required>
      <?php if($errors['email']): ?><div class="error"><?= $errors['email'] ?></div><?php endif; ?>

      <label for="userid">Usuario</label>
      <input id="userid" name="userid" value="<?= htmlspecialchars($values['userid'])?>" required>
      <?php if($errors['userid']): ?><div class="error"><?= $errors['userid'] ?></div><?php endif; ?>

      <label for="password">Contraseña</label>
      <input id="password" type="password" name="password" required>
      <?php if(!empty($pw_error)): ?><div class="error"><?= $pw_error ?></div><?php endif; ?>

      <button type="submit">Crear Revisor</button>
    </form>
    <div class="volver">
      <a href="gestion_revisiones.php">« Volver a Gestión de Revisores</a>
    </div>
  </div>
</body>
</html>
