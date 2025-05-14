<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}
$tipo   = $_SESSION['tipo_usuario'];   // 'jugador' o 'club'
$nombre = $_SESSION['usuario_nombre'];

// Verificar si es la primera vez que se accede al dashboard después del login
if (isset($_SESSION['mostrar_bienvenida']) && $_SESSION['mostrar_bienvenida']) {
    $mostrarMensaje = true;
    // Eliminar la variable de sesión para que no se muestre en futuras visitas
    unset($_SESSION['mostrar_bienvenida']);
} else {
    $mostrarMensaje = false;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | Rivales</title>
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <link rel="shortcut icon" href="../assets/img/icono rivales.png" type="image/x-icon">
</head>
<body>
  <?php include '../templates/header.php'; ?>

  <div id="blur-overlay" <?php if (!$mostrarMensaje): ?>style="display: none;"<?php endif; ?>></div>
  <h2 id="welcome-message" <?php if (!$mostrarMensaje): ?>style="display: none;"<?php endif; ?>>¡Bienvenido, <?= htmlspecialchars($nombre) ?>!</h2>

  <main>
    <?php if ($tipo === 'jugador'): ?>
      <section class="welcome">
        <p>Elegí tu próxima jugada:</p>
      </section>
      <div class="cards">
        <a href="rivales_comp.php" class="card">
          <img src="../assets/img/rival.png" alt="Buscar Rivales">
          <h3>Buscar Rivales</h3>
          <p>Encontrá rivales según categoría y aptitudes.</p>
        </a>
        <a href="reservar_cancha.php" class="card">
          <img src="../assets/img/calendar.png" alt="Reservar Cancha">
          <h3>Reservar Cancha</h3>
          <p>Asegurá tu turno en el club que más te guste.</p>
        </a>
        <a href="invitaciones_recibidas.php" class="card">
          <img src="../assets/img/recibido.png" alt="Reservar Cancha">
          <h3>Invitaciones recibidas</h3>
          <p>Revisa quien te ha invitado a jugar.</p>
        </a>
        <a href="invitaciones_enviadas.php" class="card">
          <img src="../assets/img/enviado.png" alt="Reservar Cancha">
          <h3>Invitaciones enviadas</h3>
          <p>Revisa a quien invitaste a jugar.</p>
        </a>
        <a href="../pages/mis_reservas.php" class="card">
          <img src="../assets/img/reminder.png" alt="Mis reservas">
          <h3>Mis Reservas</h3>
          <p>Revisa el horario de tus partidos</p>
        </a>
        <a href="../pages/edit_profile.php" class="card">
          <img src="../assets/img/perfil.png" alt="Mi Perfil">
          <h3>Mi Perfil</h3>
          <p>Actualizá tus datos y preferencias.</p>
        </a>
        <a href="../backend/logout.php" class="card card--logout">
          <img src="../assets/img/logout.png" alt="Cerrar Sesión">
          <h3>Cerrar Sesión</h3>
          <p>Hasta la próxima campeón!</p>
        </a>
      </div>
    <?php else: /* club */ ?>
      <section class="welcome">
        <p>Administra tu complejo con las siguientes opciones:</p>
      </section>
      <div class="cards">
        <a href="rivales_comp.php" class="card">
          <img src="../assets/img/rival.png" alt="Buscar Rivales">
          <h3>Buscar Rivales</h3>
          <p>Encontrá rivales según tu categoría y aptitudes.</p>
        </a>
        <a href="../pages/edit_profile.php" class="card">
          <img src="../assets/img/config.png" alt="Configuración">
          <h3>Configuración</h3>
          <p>Modifica datos de tu perfil y complejo.</p>
        </a>
        <a href="administrar_canchas.php" class="card">
          <img src="../assets/img/admin.png" alt="Administrar Canchas">
          <h3>Administrar Canchas</h3>
          <p>Gestiona horarios y estado de tus canchas.</p>
        </a>
        <a href="reservas_recibidas.php" class="card">
          <img src="../assets/img/calendar.png" alt="Reservas">
          <h3>Reservas</h3>
          <p>Revisa y confirma solicitudes de reserva.</p>
        </a>
        <a href="../backend/logout.php" class="card card--logout">
          <img src="../assets/img/logout.png" alt="Cerrar Sesión">
          <h3>Cerrar Sesión</h3>
          <p>Finaliza tu sesión de administración.</p>
        </a>
      </div>
    <?php endif; ?>
  </main>

  <?php include '../templates/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const overlay = document.getElementById('blur-overlay');
      const msg     = document.getElementById('welcome-message');

      <?php if ($mostrarMensaje): ?>
      setTimeout(() => {
        if (msg)     msg.classList.add('fade-out');
        if (overlay) overlay.classList.add('fade-out');

        msg.addEventListener('transitionend', () => {
          msg.remove();
          overlay.remove();
        }, { once: true });
      }, 1000);
      <?php endif; ?>
    });
  </script>
</body>
</html>
