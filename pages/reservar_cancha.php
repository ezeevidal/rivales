<?php
session_start();
// Solo jugadores pueden reservar
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'jugador') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';
date_default_timezone_set('America/Argentina/Mendoza');

// Filtros disponibles
$filters = [];
$params  = [];
$types   = '';

// Filtro por club (nombre complejo)
if (!empty($_GET['filter_club'])) {
    $filters[] = 'cl.id = ?';
    $params[]  = (int)$_GET['filter_club'];
    $types   .= 'i';
}
// Filtro por cancha (número)
if (!empty($_GET['filter_numero'])) {
    $filters[] = 'c.numero = ?';
    $params[]  = (int)$_GET['filter_numero'];
    $types   .= 'i';
}
// Filtro por suelo
if (!empty($_GET['filter_suelo'])) {
    $filters[] = "c.tipo_suelo = '" . $conn->real_escape_string($_GET['filter_suelo']) . "'";
}
// Filtro por pared
if (!empty($_GET['filter_pared'])) {
    $filters[] = "c.tipo_pared = '" . $conn->real_escape_string($_GET['filter_pared']) . "'";
}

$where = '';
if (!empty($filters)) {
    $where = 'WHERE ' . implode(' AND ', $filters);
}

// Obtener listas para filtros
date_default_timezone_set('America/Argentina/Mendoza');
$clubs = $conn->query("SELECT id, nombre_complejo FROM clubes ORDER BY nombre_complejo");
$canchas = $conn->query("SELECT DISTINCT numero FROM canchas ORDER BY numero");
$suelo_opts = ['sintetico', 'cemento'];
$pared_opts = ['cemento', 'blindex'];


// Consulta principal
$sql = "SELECT c.id, c.numero, c.tipo_suelo, c.tipo_pared, c.fraccion_horaria, c.precio, c.imagen, cl.nombre_complejo
        FROM canchas c
        JOIN clubes cl ON c.club_id = cl.id
        $where
        ORDER BY cl.nombre_complejo, c.numero";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservar Cancha | Rivales</title>
  <link rel="stylesheet" href="../assets/css/reservar_cancha.css">
  <style>
    .filter-form { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
    .filter-form div { display: flex; flex-direction: column; }
    .filter-form label { margin-bottom: .25rem; font-weight: bold; }
    .filter-form select, .filter-form input { padding: .5rem; border: 1px solid #ccc; border-radius: 4px; }
    .filter-form button { padding: .6rem 1.2rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
    .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 1rem; text-align: center; }
    .card-img { width: 100%; height: 150px; object-fit: cover; border-radius: 4px; }
    .card h3 { margin: .5rem 0; }
    .card p { margin: .25rem 0; color: #555; }
    .btn { display: inline-block; margin-top: .5rem; padding: .5rem 1rem; background: #00ff73; color: #000; border: none; border-radius: 4px; text-decoration: none; font-weight: bold; }
  </style>
</head>
<body>
  <?php include '../templates/header.php'; ?>
  <main class="container">
    <h1>Reservar una Cancha</h1>
    <form method="get" class="filter-form">
      <div>
        <label>Club:</label>
        <select name="filter_club">
          <option value="">Todos</option>
          <?php while ($c = $clubs->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= ($_GET['filter_club'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre_complejo']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label>Cancha Nº:</label>
        <select name="filter_numero">
          <option value="">Todas</option>
          <?php while ($cc = $canchas->fetch_assoc()): ?>
            <option value="<?= $cc['numero'] ?>" <?= ($_GET['filter_numero'] ?? '') == $cc['numero'] ? 'selected' : '' ?>><?= $cc['numero'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label>Suelo:</label>
        <select name="filter_suelo">
          <option value="">Todos</option>
          <?php foreach ($suelo_opts as $s): ?>
            <option value="<?= $s ?>" <?= ($_GET['filter_suelo'] ?? '') == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Pared:</label>
        <select name="filter_pared">
          <option value="">Todas</option>
          <?php foreach ($pared_opts as $p): ?>
            <option value="<?= $p ?>" <?= ($_GET['filter_pared'] ?? '') == $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit">Aplicar Filtros</button>
    </form>

    <div class="cards">
      <?php if ($result->num_rows === 0): ?>
        <p>No hay canchas que coincidan con los filtros.</p>
      <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card">
          <img src="<?= $row['imagen'] ? '../assets/uploads/' . htmlspecialchars($row['imagen']) : '../assets/img/default_complex.png' ?>" class="card-img" alt="Cancha">
          <h3><?= htmlspecialchars($row['nombre_complejo']) ?> - Cancha <?= htmlspecialchars($row['numero']) ?></h3>
          <p>Suelo: <?= htmlspecialchars($row['tipo_suelo']) ?></p>
          <p>Pared: <?= htmlspecialchars($row['tipo_pared']) ?></p>
          <p>Turno: <?= htmlspecialchars($row['fraccion_horaria']) ?></p>
          <p>Precio: $<?= htmlspecialchars($row['precio']) ?></p>
          <a href="detalle_cancha.php?id=<?= $row['id'] ?>" class="btn">Reservar</a>
        </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </main>
  <a href="dashboard.php" class="volver-btn">Volver al Dashboard</a>
  <?php include '../templates/footer.php'; ?>
</body>
</html>
