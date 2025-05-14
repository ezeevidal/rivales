<?php
session_start();
// Solo jugadores pueden ver invitaciones recibidas
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'jugador') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';
// Zona horaria
date_default_timezone_set('America/Argentina/Mendoza');

$user_id = $_SESSION['usuario_id'];

// Procesar acciones: aceptar, rechazar, eliminar
if (isset($_GET['accept'])) {
    $inv_id = (int)$_GET['accept'];
    $stmt = $conn->prepare("UPDATE invitaciones SET estado='aceptada' WHERE id = ? AND receptor_id = ?");
    $stmt->bind_param('ii', $inv_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: invitaciones_recibidas.php');
    exit;
}
if (isset($_GET['decline'])) {
    $inv_id = (int)$_GET['decline'];
    $stmt = $conn->prepare("UPDATE invitaciones SET estado='rechazada' WHERE id = ? AND receptor_id = ?");
    $stmt->bind_param('ii', $inv_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: invitaciones_recibidas.php');
    exit;
}
if (isset($_GET['delete'])) {
    $inv_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM invitaciones WHERE id = ? AND receptor_id = ?");
    $stmt->bind_param('ii', $inv_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: invitaciones_recibidas.php');
    exit;
}

// Filtros GET
$where = ['i.receptor_id = ?'];
$params = [$user_id];
$types = 'i';
// Filtrar por fecha de envío
if (!empty($_GET['filter_fecha'])) {
    $where[] = 'DATE(i.fecha_envio) = ?';
    $params[] = $_GET['filter_fecha'];
    $types .= 's';
}
// Filtrar por emisor
if (!empty($_GET['filter_sender'])) {
    $where[] = 'i.emisor_id = ?';
    $params[] = (int)$_GET['filter_sender'];
    $types .= 'i';
}
$where_sql = 'WHERE ' . implode(' AND ', $where);

// Obtener lista de emisores para filtro
$stmt_send = $conn->prepare(
    "SELECT DISTINCT u.id, u.nombre, u.apellido
     FROM usuarios u
     JOIN invitaciones i ON i.emisor_id = u.id
     WHERE i.receptor_id = ?"
);
$stmt_send->bind_param('i', $user_id);
$stmt_send->execute();
$senders = $stmt_send->get_result();
$stmt_send->close();

// Consultar invitaciones con filtros aplicados
$sql = "SELECT i.id, i.fecha_envio, i.estado, u.id AS emisor_id, u.nombre, u.apellido, u.imagen_perfil
        FROM invitaciones i
        JOIN usuarios u ON i.emisor_id = u.id
        $where_sql
        ORDER BY i.fecha_envio DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invitaciones Recibidas | Rivales</title>
  <link rel="stylesheet" href="../assets/css/invitaciones_recibidas.css">
  <style>
    .filter-form { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; }
    .filter-form div { display: flex; flex-direction: column; }
    .filter-form label { font-weight: bold; margin-bottom: .25rem; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: .5rem; text-align: center; }
    .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
    .btn { padding: .4rem .8rem; margin: 0 .2rem; border: none; border-radius: 4px; cursor: pointer; }
    .btn-accept { background: #27ae60; color: #fff; }
    .btn-decline { background: #f39c12; color: #fff; }
    .btn-delete { background: #e74c3c; color: #fff; }
  </style>
</head>
<body>
<?php include '../templates/header.php'; ?>
<main class="container">
  <h1>Invitaciones Recibidas</h1>
  <form method="get" class="filter-form">
    <div>
      <label for="filter_fecha">Fecha de Envío:</label>
      <input type="date" id="filter_fecha" name="filter_fecha" value="<?= htmlspecialchars($_GET['filter_fecha'] ?? '') ?>">
    </div>
    <div>
      <label for="filter_sender">Emisor:</label>
      <select id="filter_sender" name="filter_sender">
        <option value="">Todos</option>
        <?php while ($s = $senders->fetch_assoc()): ?>
          <option value="<?= $s['id'] ?>" <?= (int)($_GET['filter_sender'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['nombre'] . ' ' . $s['apellido']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <button type="submit" class="btn">Filtrar</button>
  </form>
  <table>
    <thead>
      <tr>
        <th>Emisor</th>
        <th>Avatar</th>
        <th>Fecha y Hora</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="5">No hay invitaciones.</td></tr>
      <?php else: while ($inv = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($inv['nombre'] . ' ' . $inv['apellido']) ?></td>
          <td><img src="<?= $inv['imagen_perfil'] ? '../assets/uploads/' . htmlspecialchars($inv['imagen_perfil']) : '../assets/img/default_profile.png' ?>" class="avatar" alt="Avatar"></td>
          <td><?= date('d/m/Y H:i', strtotime($inv['fecha_envio'])) ?></td>
          <td><?= htmlspecialchars($inv['estado']) ?></td>
          <td>
            <?php if ($inv['estado'] === 'pendiente'): ?>
              <a href="?accept=<?= $inv['id'] ?>" class="btn btn-accept">Aceptar</a>
              <a href="?decline=<?= $inv['id'] ?>" class="btn btn-decline">Rechazar</a>
            <?php endif; ?>
            <a href="?delete=<?= $inv['id'] ?>" class="btn btn-delete" onclick="return confirm('¿Eliminar invitación?');">Eliminar</a>
          </td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</main>
<?php include '../templates/footer.php'; ?>
</body>
</html>
