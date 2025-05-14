<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/conexion.php';

// Si no hay sesión, no renderizamos nada
if (!isset($_SESSION['usuario_id'])) {
    return;
}

$user_id      = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

// Imagen de perfil
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

// Conteo de pendientes
$countNot = 0;
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
        if ($nestado === 'pendiente') $countNot++;
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/header.css">
  <link rel="shortcut icon" href="../assets/img/icono rivales.png" type="image/x-icon">
</head>
<body>
<header class="top-bar">
  <div class="logo" style="margin-right: auto;">
    <a href="../pages/dashboard.php">
      <!-- Agregamos el nuevo icono 'icono rivales.png' -->
      <img src="../assets/img/icono rivales.png" alt="Rivales">
    </a>
  </div>
  <div class="spacer"></div>
  <div class="notifications" onclick="toggleNotifications()">
    <i class="fas fa-bell fa-lg"></i>
    <?php if ($countNot > 0): ?>
      <span class="badge" id="notif-count"><?= $countNot ?></span>
    <?php endif; ?>

    <div id="notifDropdown" class="notif-dropdown">
      <?php if (empty($notificaciones)): ?>
        <div class="notif-item">No hay notificaciones.</div>
      <?php else: ?>
        <?php foreach ($notificaciones as $n): ?>
          <div class="notif-item" data-id="<?= $n['id'] ?>" onclick="leerNotificacion(this)">
            <?= htmlspecialchars($n['mensaje']) ?>
            <br>
            <small><?= date('d/m/Y H:i', strtotime($n['fecha_creacion'])) ?> — <?= htmlspecialchars($n['estado']) ?></small>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="profile-dropdown">
    <img src="<?= obtenerRutaImagenPerfil($imagenArchivo) ?>" alt="Perfil" class="profile-img" id="profile-img">
    <div class="dropdown-content" id="dropdown-content">
      <!-- Cards del dashboard se generarán dinámicamente con JS -->
    </div>
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

function leerNotificacion(div) {
    const id = div.getAttribute('data-id');
    fetch('../backend/marcar_leida.php?id=' + id)
      .then(response => response.text())
      .then(() => {
          div.style.transition = 'background-color 0.3s ease, opacity 0.5s ease-out';
          div.style.backgroundColor = 'rgba(0, 255, 115, 0.2)';
          div.style.opacity = '0';

          setTimeout(() => {
              div.remove();
              const badge = document.getElementById('notif-count');
              if (badge) {
                  let c = parseInt(badge.innerText);
                  c = Math.max(0, c-1);
                  if (c === 0) {
                      badge.remove();
                      document.getElementById('notifDropdown').innerHTML = '<div class="notif-item">No hay notificaciones.</div>';
                  } else {
                      badge.innerText = c;
                  }
              }

              const mensaje = div.innerText.toLowerCase();
              if (mensaje.includes('invitado')) {
                  window.location.href = '../pages/invitaciones_recibidas.php';
              } else if (mensaje.includes('aceptó') || mensaje.includes('rechazó')) {
                  window.location.href = '../pages/invitaciones_enviadas.php';
              } else {
                  window.location.href = '../pages/notificaciones.php';
              }
          }, 500);
      });
}

// Generar las cards dinámicamente en el dropdown
const cards = [
    { title: "Buscar Rivales", link: "rivales_comp.php", description: "Encontrá rivales según categoría y aptitudes.", img: "../assets/img/rival.png" },
    { title: "Reservar Cancha", link: "reservar_cancha.php", description: "Asegurá tu turno en el club que más te guste.", img: "../assets/img/calendar.png" },
    { title: "Invitaciones recibidas", link: "invitaciones_recibidas.php", description: "Revisa quien te ha invitado a jugar.", img: "../assets/img/recibido.png" },
    { title: "Invitaciones enviadas", link: "invitaciones_enviadas.php", description: "Revisa a quien invitaste a jugar.", img: "../assets/img/enviado.png" },
    { title: "Mis Reservas", link: "../pages/mis_reservas.php", description: "Revisa el horario de tus partidos", img: "../assets/img/reminder.png" },
    { title: "Mi Perfil", link: "../pages/edit_profile.php", description: "Actualizá tus datos y preferencias.", img: "../assets/img/perfil.png" },
    { title: "Cerrar Sesión", link: "../backend/logout.php", description: "Hasta la próxima campeón!", img: "../assets/img/logout.png" }
];

const dropdownContent = document.getElementById('dropdown-content');
cards.forEach(card => {
    const cardElement = document.createElement('a');
    cardElement.href = card.link;
    cardElement.classList.add('dropdown-card');
    cardElement.innerHTML = `
      <img src="${card.img}" alt="${card.title}" class="dropdown-card-img">
      <div>
        <h3>${card.title}</h3>
        <p>${card.description}</p>
      </div>
    `;
    dropdownContent.appendChild(cardElement);
});

// Mostrar u ocultar el dropdown al hacer clic en la imagen de perfil
document.getElementById('profile-img').addEventListener('click', function() {
    const dropdown = document.getElementById('dropdown-content');
    dropdown.classList.toggle('show');
});
</script>
</body>
</html>
