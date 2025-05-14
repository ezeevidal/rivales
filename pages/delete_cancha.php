<?php
session_start();
// Solo clubs pueden eliminar
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario']!=='club') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';

if (!isset($_GET['id'])) {
    header('Location: administrar_canchas.php');
    exit;
}

$id = (int) $_GET['id'];
$clubId = (int) $_SESSION['usuario_id'];

// Borrado seguro: solo la propia cancha del club
$stmt = $conn->prepare("DELETE FROM canchas WHERE id = ? AND club_id = ?");
$stmt->bind_param('ii', $id, $clubId);
if ($stmt->execute()) {
    // opcional: podrías revisar $stmt->affected_rows para confirmar
    header('Location: administrar_canchas.php');
    exit;
} else {
    die('Error al eliminar cancha: '.htmlspecialchars($stmt->error));
}
