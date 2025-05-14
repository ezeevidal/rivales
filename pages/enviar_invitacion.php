<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/conexion.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    return;
}
$user_id      = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

// --- Imagen de perfil ---
$imagenArchivo = $_SESSION['imagen_perfil'] ?? '';
if (empty($imagenArchivo)) {
    $tbl = ($tipo_usuario === 'jugador') ? 'usuarios' : 'clubes';
    $col = ($tipo_usuario === 'jugador') ? 'imagen_perfil' : 'imagen_complejo';
    if ($stmt_img = $conn->prepare("SELECT $col FROM $tbl WHERE id = ?")) {
        $stmt_img->bind_param('i', $user_id);
        $stmt_img->execute();
        $stmt_img->bind_result($img_db);
        if ($stmt_img->fetch()) {
            $imagenArchivo = $img_db;
            $_SESSION['imagen_perfil'] = $img_db;
        }
        $stmt_img->close();
    }
}

// --- Conteo notificaciones pendientes ---
$countNot = 0;
if ($stmtCount = $conn->prepare(
    "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND estado = 'pendiente'"
)) {
    $stmtCount->bind_param('i', $user_id);
    $stmtCount->execute();
    $stmtCount->bind_result($countNot);
    $stmtCount->fetch();
    $stmtCount->close();
}

// --- Cargar todas las notificaciones ---
$notificaciones = [];
if ($stmt = $conn->prepare(
    "SELECT id, mensaje, estado, fecha_creacion
     FROM notificaciones
     WHERE usuario_id = ?
     ORDER BY fecha_creacion DESC"
)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($nid, $nmensaje, $nestado, $nfecha);
    while ($stmt->fetch()) {
        $notificaciones[] = [
            'id'             => $nid,
            'mensaje'        => $nmensaje,
            'estado'         => $nestado,
            'fecha_creacion' => $nfecha
        ];
    }
    $stmt->close();
}

function obtenerRutaImagenPerfil($nombreArchivo) {
    if (!empty($nombreArchivo)) {
        return '../assets/uploads/' . htmlspecialchars($nombreArchivo);
    }
    return '../assets/img/default_profile.png';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../templates/header.css">
  <link rel="shortcut icon" href="../assets/img/icono rivales.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .top-bar { position: relative; z-index: 1000; display: flex; align-items: center; padding: 0.5rem 1rem; background: #222; }
    .logo img { height: 40px; }
    .spacer { flex: 1; }
    .notifications { position: relative; margin-right: 1rem; cursor: pointer; color: #fff; }
    .notifications .badge { position: absolute; top: -5px; right: -5px; background: #ff3b3b; color: #fff; border-radius: 50%; padding: 2px 6px; font-size: 0.75rem; }
    .notif-dropdown { display: none; position: absolute; right: 0; top: 120%; background: #fff; color: #333; width: 320px; max-height: 300px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 4px; z-index: 2000; }
    .notif-dropdown.active { display: block; }
    .notif-item { display: block; padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem; color: #333; text-decoration: none; }
    .notif-item:last-child { border-bottom: none; }
    .notif-item.read { background: #f5f5f5; }
    .notif-item:hover { background: #eef; }
    .user-info { display: flex; align-items: center; color: #fff; }
    .user-info span { margin-right: 0.5rem; }
    .user-info img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
  </style>
</head>
<body>
<header class="top-bar">
  <div class="logo">
    <a href="../pages/dashboard.php"><img src="../assets/img/logo_full_white.png" alt="Rivales"></a>
  </div>
  <div class="spacer"></div>
  <div class="notifications" onclick="toggleNotifications()">
    <i class="fas fa-bell fa-lg"></i>
    <?php if ($countNot > 0): ?><span class="badge"><?= $countNot ?></span><?php endif; ?>
    <div id="notifDropdown" class="notif-dropdown">
      <?php if (empty($notificaciones)): ?>
        <span class="notif-item">No hay notificaciones.</span>
      <?php else: ?>
        <?php foreach ($notificaciones as $n):
            // Determinar destino
            if (strpos($n['mensaje'], 'Te han invitado') === 0) {
                $url = 'invitaciones_recibidas.php';
            } elseif (strpos($n['mensaje'], 'Invitaste a') === 0) {
                $url = 'invitaciones_enviadas.php';
            } else {
                $url = 'notificaciones.php';
            }
        ?>
          <a href="../pages/<?= $url ?>" class="notif-item <?= $n['estado']==='leida' ? 'read' : '' ?>">
            <?= htmlspecialchars($n['mensaje']) ?>
            <br><small><?= date('d/m/Y H:i', strtotime($n['fecha_creacion'])) ?> - <?= htmlspecialchars($n['estado']) ?></small>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  <div class="user-info">
    <span><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
    <img src="<?= obtenerRutaImagenPerfil($imagenArchivo) ?>" alt="Perfil">
  </div>
</header>
<script>
  function toggleNotifications() {
    document.getElementById('notifDropdown').classList.toggle('active');
  }
  document.addEventListener('click', function(e) {
    const dd = document.getElementById('notifDropdown');
    const bell = document.querySelector('.notifications');
    if (bell && !bell.contains(e.target)) {
      dd.classList.remove('active');
    }
  });
</script>
