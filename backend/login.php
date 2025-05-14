<?php
session_start();
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$email      = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Completa todos los campos.';
    header('Location: ../index.php');
    exit;
}

// Intento como jugador
$sql    = 'SELECT id, nombre, password FROM usuarios WHERE email = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

$tipo_usuario = null;
if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $nombre, $hashed);
    $stmt->fetch();
    $tipo_usuario = 'jugador';
} else {
    // Intento como club
    $stmt->close();
    $sql    = 'SELECT id, nombre_encargado, password FROM clubes WHERE email_encargado = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $nombre, $hashed);
        $stmt->fetch();
        $tipo_usuario = 'club';
    }
}

if ($tipo_usuario === null) {
    $_SESSION['login_error'] = 'Usuario no encontrado.';
    header('Location: ../index.php');
    exit;
}

if (!password_verify($password, $hashed)) {
    $_SESSION['login_error'] = 'Contraseña incorrecta.';
    header('Location: ../index.php');
    exit;
}

// Login exitoso
$_SESSION['usuario_id']     = $id;
$_SESSION['usuario_nombre'] = $nombre;
$_SESSION['tipo_usuario']   = $tipo_usuario;

// ¡Aquí agregas la variable de sesión para mostrar el mensaje!
$_SESSION['mostrar_bienvenida'] = true;

header('Location: ../pages/dashboard.php');
exit;