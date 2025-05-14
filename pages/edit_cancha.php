<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'club') {
    header('Location: ../index.php'); exit;
}
require_once __DIR__ . '/../backend/conexion.php';
$error = '';

$clubId = (int)$_SESSION['usuario_id'];

if (!isset($_GET['id'])) {
    header('Location: administrar_canchas.php');
    exit;
}

$id = (int)$_GET['id'];

// Cargar datos de la cancha
$stmt = $conn->prepare("SELECT numero, tipo_suelo, tipo_pared, fraccion_horaria, dias_disponibles, precio, imagen FROM canchas WHERE id=? AND club_id=?");
$stmt->bind_param('ii', $id, $clubId);
$stmt->execute();
$stmt->bind_result($numero, $suelo, $pared, $fraccion, $dias_disponibles_json, $precio, $imagen_actual);
if (!$stmt->fetch()) {
    die("Cancha no encontrada o no tienes permiso.");
}
$stmt->close();

$dias_disp = json_decode($dias_disponibles_json, true) ?: [];

// Función para generar horarios de 1:30hs desde las 6:00
function generar_horarios() {
    $inicio = strtotime('06:00');
    $fin    = strtotime('23:00');
    $franja = 90 * 60; // 1h 30m
    $horarios = [];
    for ($h = $inicio; $h <= $fin; $h += $franja) {
        $horarios[] = date('H:i', $h);
    }
    return $horarios;
}

// Procesar edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero     = (int)($_POST['numero'] ?? 0);
    $suelo      = $_POST['tipo_suelo'] ?? '';
    $pared      = $_POST['tipo_pared'] ?? '';
    $fraccion   = $_POST['fraccion_horaria'] ?? '';
    $precio     = (int)($_POST['precio'] ?? 0);
    
    // Recolectar días
    $dias = [];
    foreach (['lunes','martes','miercoles','jueves','viernes','sabado','domingo'] as $dia) {
        if (!empty($_POST['dias'][$dia]['activo'])) {
            $from = $_POST['dias'][$dia]['desde'] ?? '';
            $to   = $_POST['dias'][$dia]['hasta'] ?? '';
            $dias[$dia] = ['desde' => $from, 'hasta' => $to];
        }
    }
    $dias_json = json_encode($dias);

    // Imagen
    $imagen = $_POST['imagen_actual'] ?? null;
    if (!empty($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], __DIR__ . '/../assets/uploads/' . $imagen);
    }

    // Guardar cambios
    $sql = "UPDATE canchas SET numero=?, tipo_suelo=?, tipo_pared=?, fraccion_horaria=?, dias_disponibles=?, precio=?, imagen=? WHERE id=? AND club_id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die('Error en preparación: '.htmlspecialchars($conn->error));
    $stmt->bind_param('issssissi', $numero, $suelo, $pared, $fraccion, $dias_json, $precio, $imagen, $id, $clubId);
    
    if ($stmt->execute()) {
        header('Location: administrar_canchas.php');
        exit;
    } else {
        $error = 'Error al actualizar: ' . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Cancha | Rivales</title>
  <link rel="stylesheet" href="../assets/css/add_cancha.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<main class="form-container">
    <h2>Editar Cancha N°<?= htmlspecialchars($numero) ?></h2>
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>

    <form action="edit_cancha.php?id=<?= htmlspecialchars($id) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($imagen_actual) ?>">

        <label>Cancha N°</label>
        <select name="numero" required>
            <?php for ($i = 1; $i <= 20; $i++): ?>
            <option value="<?= $i ?>" <?= $i == $numero ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>

        <label>Tipo de Suelo</label>
        <select name="tipo_suelo" required>
            <option value="sintetico" <?= $suelo === 'sintetico' ? 'selected' : '' ?>>Sintético</option>
            <option value="cemento" <?= $suelo === 'cemento' ? 'selected' : '' ?>>Cemento</option>
        </select>

        <label>Tipo de Pared</label>
        <select name="tipo_pared" required>
            <option value="cemento" <?= $pared === 'cemento' ? 'selected' : '' ?>>Cemento</option>
            <option value="blindex" <?= $pared === 'blindex' ? 'selected' : '' ?>>Blindex</option>
        </select>

        <label>Fracción Horaria</label>
        <select name="fraccion_horaria" required>
            <option value="01:30" <?= $fraccion === '01:30' ? 'selected' : '' ?>>1h 30m</option>
        </select>

        <label>Días Disponibles y Horarios</label>
        <div class="days">
            <?php foreach (['lunes','martes','miercoles','jueves','viernes','sabado','domingo'] as $dia): 
                $activo = isset($dias_disp[$dia]);
                $desde  = $activo ? $dias_disp[$dia]['desde'] : '';
                $hasta  = $activo ? $dias_disp[$dia]['hasta'] : '';
            ?>
            <div class="day">
                <label>
                    <input type="checkbox" name="dias[<?= $dia ?>][activo]" value="1" <?= $activo ? 'checked' : '' ?>>
                    <?= ucfirst($dia) ?>
                </label>
                <div class="day-hours">
                    <label>Desde:
                        <select name="dias[<?= $dia ?>][desde]">
                            <?php foreach (generar_horarios() as $hora): ?>
                            <option value="<?= $hora ?>" <?= $hora == $desde ? 'selected' : '' ?>><?= $hora ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Hasta:
                        <select name="dias[<?= $dia ?>][hasta]">
                            <?php foreach (generar_horarios() as $hora): ?>
                            <option value="<?= $hora ?>" <?= $hora == $hasta ? 'selected' : '' ?>><?= $hora ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <label>Precio ($)</label>
        <input type="number" name="precio" value="<?= htmlspecialchars($precio) ?>" min="0" step="1" required>

        <label>Imagen de la Cancha</label>
        <input type="file" name="imagen" accept="image/*">

        <button type="submit" class="btn">Guardar Cambios</button>
        <a href="administrar_canchas.php" class="btn" style="background: #6c757d;">Cancelar</a>
    </form>
</main>
<a href="dashboard.php" class="volver-btn">Volver al Dashboard</a>
<?php include '../templates/footer.php'; ?>
</body>
</html>
