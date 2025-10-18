<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "test");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$usuario = $_POST['usuario'];
$password = $_POST['password'];
$rol = $_POST['rol'];

$sql = "SELECT * FROM miembro WHERE userid_miembro = ? AND password_miembro = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $usuario, $password);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $miembro = $resultado->fetch_assoc();

    $rol_valido = false;

    if ($rol === "autor" && $miembro["es_autor"] == 1) $rol_valido = true;
    if ($rol === "revisor" && $miembro["es_revisor"] == 1) $rol_valido = true;
    if ($rol === "admin" && $miembro["es_admin"] == 1) $rol_valido = true;

    if ($rol_valido) {
        $_SESSION["usuario"] = $usuario;
        $_SESSION["rut_miembro"] = $miembro["rut_miembro"];
        $_SESSION["rol"] = $rol;
        $_SESSION["nombre_miembro"] = $miembro["nombre_miembro"];

        if ($rol === "autor") {
            header("Location: autor_home.php");
        } elseif ($rol === "revisor") {
            header("Location: revisor_home.php");
        } elseif ($rol === "admin") {
            header("Location: admin_home.php");
        }
        exit;
    } else {
        echo "<script>alert('El usuario no es $rol.'); window.location.href='login.php';</script>";
    }
} else {
    echo "<script>alert('Usuario o contraseña incorrectos.'); window.location.href='login.php';</script>";
}
?>
