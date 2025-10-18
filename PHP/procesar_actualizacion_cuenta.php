<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$userid   = $_SESSION['usuario'];
$nombre   = trim($_POST['nombre']);
$email    = trim($_POST['email']);
$password = trim($_POST['password']);
$nuevoRol = $_POST['rol'];

$mysqli = new mysqli('localhost','root','','test');
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

$stmt = $mysqli->prepare("
    UPDATE miembro
       SET nombre_miembro   = ?,
           email_miembro    = ?,
           password_miembro = ?
     WHERE userid_miembro  = ?
");
$stmt->bind_param('ssss', $nombre, $email, $password, $userid);
$stmt->execute();
$stmt->close();

$_SESSION['nombre_miembro'] = $nombre;
if (in_array($nuevoRol, ['autor','revisor','admin'], true)) {
    $_SESSION['rol'] = $nuevoRol;
}

$mysqli->close();
header('Location: cuenta.php');
exit;
