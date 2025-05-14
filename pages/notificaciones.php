<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';

if (!isset($_GET['id'])) {
    header('Location: ../pages/dashboard.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$noti_id = (int) $_GET['id'];

// Traemos el mensaje de la notificación
$stmt = $conn->prepare("SELECT mensaje FROM notificaciones WHERE id = ? AND usuario_id = ?");
$stmt->bind_param('ii', $noti_id, $usuario_id);
$stmt->execute();
$stmt->bind_result($mensaje);
if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: ../pages/dashboard.php');
    exit;
}
$stmt->close();

// Marcamos la notificación como "aceptada" (o "vista")
$stmt = $conn->prepare("UPDATE notificaciones SET estado = 'aceptada' WHERE id = ? AND usuario_id = ?");
$stmt->bind_param('ii', $noti_id, $usuario_id);
$stmt->execute();
$stmt->close();

// Según el contenido del mensaje, redirigimos
if (strpos($mensaje, 'invitado') !== false) {
    header('Location: ../pages/invitaciones_recibidas.php');
} elseif (strpos($mensaje, 'aceptado') !== false || strpos($mensaje, 'rechazado') !== false) {
    header('Location: ../pages/invitaciones_enviadas.php');
} else {
    header('Location: ../pages/dashboard.php');
}
exit;
?>
