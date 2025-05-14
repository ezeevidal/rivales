<?php
session_start();
// Solo jugadores pueden ver sus reservas
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'jugador') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';
date_default_timezone_set('America/Argentina/Mendoza');

$jugador_id = $_SESSION['usuario_id'];

// Procesar acciones: cancelar, eliminar o eliminar todas canceladas
if (isset($_GET['cancel'])) {
    $res_id = (int)$_GET['cancel'];
    $stmt = $conn->prepare("UPDATE reservas SET estado='cancelada' WHERE id = ? AND jugador_id = ?");
    $stmt->bind_param('ii', $res_id, $jugador_id);
    $stmt->execute();
    $stmt->close();
    header('Location: mis_reservas.php'); exit;
}
if (isset($_GET['delete'])) {
    $res_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM reservas WHERE id = ? AND jugador_id = ?");
    $stmt->bind_param('ii', $res_id, $jugador_id);
    $stmt->execute();
    $stmt->close();
    header('Location: mis_reservas.php'); exit;
}
// Eliminar todas las reservas canceladas del jugador
if (isset($_GET['delete_all']) && $_GET['delete_all'] == '1') {
    $stmt = $conn->prepare("DELETE FROM reservas WHERE jugador_id = ? AND estado = 'cancelada'");
    $stmt->bind_param('i', $jugador_id);
    $stmt->execute();
    $stmt->close();
    header('Location: mis_reservas.php'); exit;
}

// Filtros
$where = ['r.jugador_id = ?'];
$params = [$jugador_id];
$types = 'i';

if (!empty($_GET['filter_fecha'])) {
    $where[] = 'r.fecha = ?';
    $params[] = $_GET['filter_fecha'];
    $types .= 's';
}
if (!empty($_GET['filter_cancha'])) {
    $where[] = 'c.numero = ?';
    $params[] = (int)$_GET['filter_cancha'];
    $types .= 'i';
}
if (!empty($_GET['filter_club'])) {
    $where[] = 'cl.id = ?';
    $params[] = (int)$_GET['filter_club'];
    $types .= 'i';
}

$where_sql = implode(' AND ', $where);

// Obtener lista de clubs y canchas para filtros
$clubs = $conn->query("SELECT id, nombre_complejo FROM clubes ORDER BY nombre_complejo");
$canchas = $conn->query("SELECT DISTINCT c.numero FROM canchas c
    JOIN reservas r ON r.cancha_id = c.id
    WHERE r.jugador_id = $jugador_id ORDER BY c.numero");

// Preparar consulta de reservas
$sql = "SELECT r.id, r.fecha, r.hora_inicio, r.hora_fin, c.numero, cl.nombre_complejo, r.estado
        FROM reservas r
        JOIN canchas c ON r.cancha_id = c.id
        JOIN clubes cl ON c.club_id = cl.id
        WHERE $where_sql
        ORDER BY r.fecha DESC, r.hora_inicio DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mis Reservas | Rivales</title>
  <link rel="stylesheet" href="../assets/css/mis_reservas.css">
</head>
<body>
<?php include '../templates/header.php'; ?>
<main class="container">
  <h1>Mis Reservas</h1>
  <form method="get" class="filter-form">
    <div>
      <label>Fecha:</label>
      <input type="date" name="filter_fecha" value="<?= htmlspecialchars($_GET['filter_fecha'] ?? '') ?>">
    </div>
    <div>
      <label>Cancha:</label>
      <select name="filter_cancha">
        <option value="">Todas</option>
        <?php while ($row = $canchas->fetch_assoc()): ?>
          <option value="<?= $row['numero'] ?>" <?= (int)($_GET['filter_cancha'] ?? 0) === (int)$row['numero'] ? 'selected' : '' ?>>Cancha <?= $row['numero'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div>
      <label>Club:</label>
      <select name="filter_club">
        <option value="">Todos</option>
        <?php while ($row = $clubs->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>" <?= (int)($_GET['filter_club'] ?? 0) === (int)$row['id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['nombre_complejo']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <button type="submit" class="btn">Filtrar</button>
    <button type="submit" name="delete_all" value="1" class="btn btn-delete-all" onclick="return confirm('¿Eliminar todas las reservas canceladas?');">Eliminar todas canceladas</button>
  </form>

  <table class="reservas-table">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Hora Inicio</th>
        <th>Hora Fin</th>
        <th>Cancha</th>
        <th>Club</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="7">No hay reservas.</td></tr>
      <?php else: ?>
        <?php while ($res = $result->fetch_assoc()): ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($res['fecha'])) ?></td>
            <td><?= htmlspecialchars($res['hora_inicio']) ?></td>
            <td><?= htmlspecialchars($res['hora_fin']) ?></td>
            <td><?= htmlspecialchars($res['numero']) ?></td>
            <td><?= htmlspecialchars($res['nombre_complejo']) ?></td>
            <td><?= htmlspecialchars($res['estado']) ?></td>
            <td>
              <?php if ($res['estado'] === 'confirmada'): ?>
                <a href="mis_reservas.php?cancel=<?= $res['id'] ?>" class="btn btn-cancel">Cancelar</a>
              <?php endif; ?>
              <a href="mis_reservas.php?delete=<?= $res['id'] ?>" class="btn btn-delete" onclick="return confirm('Eliminar esta reserva?');">Eliminar</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
    </tbody>
  </table>
</main>
<a href="dashboard.php" class="volver-btn">Volver al Dashboard</a>
<?php include '../templates/footer.php'; ?>
</body>
</html>
