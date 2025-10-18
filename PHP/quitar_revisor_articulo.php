<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol']!=='admin') {
  header('Location: login.php'); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $id   = intval($_POST['id_articulo']);
  $rut  = $_POST['rut_miembro'];
  $mysqli = new mysqli('localhost','root','','test');
  if ($mysqli->connect_error) die('Error BD');
  $st = $mysqli->prepare("
    DELETE FROM detalle_revision 
     WHERE id_articulo=? AND rut_miembro=?
  ");
  $st->bind_param('is',$id,$rut);
  $st->execute();
  $st->close();
  header("Location: gestionar_asignacion_revisores.php");
  exit;
}
header("HTTP/1.1 400 Bad Request");
