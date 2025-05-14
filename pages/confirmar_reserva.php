<?php
session_start();
// Solo jugadores
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'jugador') {
    session_destroy();
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';
// Zona horaria
date_default_timezone_set('America/Argentina/Mendoza');

// Debe ser POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reservar_cancha.php');
    exit;
}

// Datos de la reserva
$jugador_id  = $_SESSION['usuario_id'];
$cancha_id   = (int)($_POST['cancha_id'] ?? 0);
$fecha       = $_POST['fecha'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';

// Verificar turno duplicado
$stmt = $conn->prepare("SELECT COUNT(*) FROM reservas WHERE cancha_id = ? AND fecha = ? AND hora_inicio = ? AND estado = 'confirmada'");
$stmt->bind_param('iss', $cancha_id, $fecha, $hora_inicio);
$stmt->execute();
$stmt->bind_result($dup_count);
$stmt->fetch();
$stmt->close();
if ($dup_count > 0) {
    // Mostrar error y volver con fondo blureado
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Turno Ocupado</title>
      <style>
        /* Overlay blur */
        #blur-overlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(255, 255, 255, 0.56); /* Más transparente */
          backdrop-filter: blur(8px);
          -webkit-backdrop-filter: blur(8px);
          z-index: 999;
        }
        .toast {
          position: fixed;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          background: #e74c3c;
          color: #fff;
          padding: 1.5rem 2.5rem;
          border-radius: 8px;
          box-shadow: 0 2px 12px rgba(0,0,0,0.3);
          font-size: 1.1rem;
          opacity: 0;
          transition: opacity 0.5s ease;
          z-index: 1000;
        }
        .toast.show { opacity: 1; }
      </style>
    </head>
    <body>
      <?php include '../templates/header.php'; ?>
      <div id="blur-overlay"></div>
      <div class="toast" id="toast">Este turno ya está reservado.</div>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const toast = document.getElementById('toast');
          toast.classList.add('show');
          setTimeout(() => {
            toast.classList.remove('show');
            window.location.href = 'detalle_cancha.php?id=' + <?= $cancha_id ?>;
          }, 2000);
        });
      </script>
    </body>
    </html>
    <?php
    exit;
}

// Obtener duración de la cancha
$stmt = $conn->prepare("SELECT fraccion_horaria FROM canchas WHERE id = ?");
$stmt->bind_param('i', $cancha_id);
$stmt->execute();
$stmt->bind_result($fraccion);
$stmt->fetch();
$stmt->close();

// Calcular hora de fin
list($h, $m) = explode(':', $fraccion);
$intervalSpec = 'PT' . intval($h) . 'H' . intval($m) . 'M';
$dt = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . $hora_inicio);
$hora_fin = $dt->add(new DateInterval($intervalSpec))->format('H:i');

// Insertar reserva
$stmt = $conn->prepare("INSERT INTO reservas (cancha_id, jugador_id, fecha, hora_inicio, hora_fin) VALUES (?,?,?,?,?)");
$stmt->bind_param('iisss', $cancha_id, $jugador_id, $fecha, $hora_inicio, $hora_fin);
$success = $stmt->execute();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reserva <?= $success ? 'Exitosa' : 'Fallida' ?></title>
  <style>
    #blur-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(5px);
      background: rgba(0, 0, 0, 0.3);
      z-index: 1000;
    }
    .toast {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: <?= $success ? '#00ff73' : '#e74c3c' ?>;
      color: #000;
      padding: 1.5rem 2.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.3);
      font-size: 1.1rem;
      opacity: 0;
      transition: opacity 0.5s ease;
      z-index: 1000;
    }
    .toast.show { opacity: 1; }
  </style>
</head>
<body>
  <?php include '../templates/header.php'; ?>
  <div id="blur-overlay"></div>
  <div class="toast" id="toast"><?= $success ? 'Cancha reservada con éxito' : 'Error al procesar la reserva' ?></div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toast = document.getElementById('toast');
      toast.classList.add('show');
      setTimeout(() => {
        toast.classList.remove('show');
        window.location.href = 'dashboard.php';
      }, 2000);
    });
  </script>
</body>
</html>
