<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'jugador') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';

$user_id = $_SESSION['usuario_id'];

$sql = "SELECT i.id, i.fecha_envio, i.estado, u.id AS receptor_id, u.nombre, u.apellido, u.imagen_perfil,
               r.fecha AS reserva_fecha, r.hora_inicio, c.numero AS cancha_numero, cl.nombre_complejo
        FROM invitaciones i
        JOIN usuarios u ON i.receptor_id = u.id
        JOIN reservas r ON i.reserva_id = r.id
        JOIN canchas c ON r.cancha_id = c.id
        JOIN clubes cl ON c.club_id = cl.id
        WHERE i.emisor_id = ?
        ORDER BY i.fecha_envio DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Invitaciones Enviadas | Rivales</title>
  <link rel="stylesheet" href="../assets/css/invitaciones_enviadas.css">
  <link rel="shortcut icon" href="../assets/img/icono rivales.png" type="image/x-icon">
</head>
<body>
<?php include '../templates/header.php'; ?>
<main class="container">
  <h1>Invitaciones Enviadas</h1>
  <table>
    <thead>
      <tr>
        <th>Receptor</th>
        <th>Avatar</th>
        <th>Reserva</th>
        <th>Fecha de Envío</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($inv = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($inv['nombre'].' '.$inv['apellido']) ?></td>
          <td><img src="<?= $inv['imagen_perfil'] ? '../assets/uploads/'.htmlspecialchars($inv['imagen_perfil']) : '../assets/img/default_profile.png' ?>" class="avatar" alt="avatar"></td>
          <td><?= date('d/m/Y', strtotime($inv['reserva_fecha'])) ?> <?= $inv['hora_inicio'] ?> - <?= htmlspecialchars($inv['nombre_complejo']) ?> (Cancha <?= $inv['cancha_numero'] ?>)</td>
          <td><?= date('d/m/Y H:i', strtotime($inv['fecha_envio'])) ?></td>
          <td><?= htmlspecialchars($inv['estado']) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</main>
<?php include '../templates/footer.php'; ?>
</body>
</html>
