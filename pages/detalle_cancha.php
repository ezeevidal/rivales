<?php
session_start();
// Ajustar zona horaria a Argentina/Mendoza
date_default_timezone_set('America/Argentina/Mendoza');
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'jugador') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';

// Forzar locale en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'Spanish');

if (!isset($_GET['id'])) {
    header('Location: reservar_cancha.php');
    exit;
}

$id_cancha = (int) $_GET['id'];

// Obtener detalles de la cancha
$sql = "SELECT c.numero, c.tipo_suelo, c.fraccion_horaria, c.dias_disponibles, c.precio, c.imagen, cl.nombre_complejo
        FROM canchas c
        INNER JOIN clubes cl ON c.club_id = cl.id
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_cancha);
$stmt->execute();
$stmt->bind_result($numero, $suelo, $fraccion_horaria, $dias_disponibles_json, $precio, $imagen, $nombre_complejo);
if (!$stmt->fetch()) {
    die("Cancha no encontrada.");
}
$stmt->close();

// Decodificar JSON de días disponibles
$dias_disponibles = json_decode($dias_disponibles_json, true) ?: [];

// Generar horarios para los próximos 7 días
$horarios_por_fecha = [];
for ($offset = 0; $offset <= 7; $offset++) {
    $fecha = date('Y-m-d', strtotime("+$offset day"));
    $ts_fecha = strtotime($fecha);
    $dia_semana = strtolower(strftime('%A', $ts_fecha));

    if (!isset($dias_disponibles[$dia_semana])) {
        continue;
    }
    // Apertura y cierre en timestamps del día
    list($h_op, $m_op) = explode(':', $dias_disponibles[$dia_semana]['desde']);
    list($h_cl, $m_cl) = explode(':', $dias_disponibles[$dia_semana]['hasta']);
    $open_ts = mktime((int)$h_op, (int)$m_op, 0, date('m', $ts_fecha), date('d', $ts_fecha), date('Y', $ts_fecha));
    $close_ts = mktime((int)$h_cl, (int)$m_cl, 0, date('m', $ts_fecha), date('d', $ts_fecha), date('Y', $ts_fecha));

    $horas = intval(substr($fraccion_horaria, 0, 2));
    $minutos = intval(substr($fraccion_horaria, 3, 2));
    $interval_sec = $horas * 3600 + $minutos * 60;

    $now_ts = time();
    $slots = floor(($close_ts - $open_ts) / $interval_sec);

    for ($i = 0; $i <= $slots; $i++) {
        $ts_slot = $open_ts + $i * $interval_sec;
        // Si es hoy, filtrar slots pasados
        if ($offset === 0 && $ts_slot < $now_ts) {
            continue;
        }
        $horarios_por_fecha[$fecha][] = date('H:i', $ts_slot);
    }
}

function formatear_fecha($f) {
    $p = explode('-', $f);
    return sprintf('%02d/%02d/%04d', $p[2], $p[1], $p[0]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservar Cancha #<?= htmlspecialchars($numero) ?> | Rivales</title>
  <link rel="stylesheet" href="../assets/css/reservar_cancha.css">
  <style>
    .loading { text-align:center; font-size:1rem; color:#555; margin:8px 0; }
  </style>
</head>
<body>
<?php include '../templates/header.php'; ?>
<main class="container">
  <h1>Reservar Cancha #<?= htmlspecialchars($numero) ?> en <?= htmlspecialchars($nombre_complejo) ?></h1>
  <div class="card">
    <img src="<?= $imagen ? '../assets/uploads/' . htmlspecialchars($imagen) : '../assets/img/default_complex.png' ?>" alt="Imagen Cancha" class="card-img">
    <p>Tipo de suelo: <?= htmlspecialchars($suelo) ?></p>
    <p>Precio: $<?= htmlspecialchars($precio) ?></p>

    <?php if (empty($horarios_por_fecha)): ?>
      <p style="color:red;">No hay horarios disponibles en los próximos 7 días.</p>
    <?php else: ?>
      <form action="confirmar_reserva.php" method="POST">
        <input type="hidden" name="cancha_id" value="<?= htmlspecialchars($id_cancha) ?>">
        <label>Seleccionar fecha:</label>
        <select name="fecha" id="fecha" required onchange="filtrarHorarios(this.value)">
          <?php foreach ($horarios_por_fecha as $f => $_): ?>
            <option value="<?= htmlspecialchars($f) ?>"><?= formatear_fecha($f) ?></option>
          <?php endforeach; ?>
        </select>
        <div id="loading" class="loading" style="display:none;">Cargando horarios...</div>
        <label>Seleccionar horario:</label>
        <select name="hora_inicio" id="hora_inicio" required></select>
        <button type="submit" class="btn">Confirmar Reserva</button>
      </form>

      <script>
        const data = <?= json_encode($horarios_por_fecha) ?>;
        const selFecha = document.getElementById('fecha');
        const selHora = document.getElementById('hora_inicio');
        const loading = document.getElementById('loading');

        function filtrarHorarios(fecha) {
          selHora.innerHTML = '';
          loading.style.display = 'block';
          setTimeout(() => {
            (data[fecha] || []).forEach(h => {
              const o = document.createElement('option'); o.value = h; o.textContent = h;
              selHora.appendChild(o);
            });
            loading.style.display = 'none';
          }, 200);
        }
        document.addEventListener('DOMContentLoaded', () => filtrarHorarios(selFecha.value));
      </script>
    <?php endif; ?>
  </div>
</main>
<a href="dashboard.php" class="volver-btn">Volver al Dashboard</a>
<?php include '../templates/footer.php'; ?>
</body>
</html>
