<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$id_notif = (int)($_GET['id'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

if ($id_notif > 0) {
    $stmt = $conn->prepare("UPDATE notificaciones SET estado = 'leida' WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param('ii', $id_notif, $usuario_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
