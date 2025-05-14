<?php
session_start();
// Verificar que el usuario esté logueado y sea club
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'club') {
    header('Location: ../index.php'); exit;
}
require_once __DIR__ . '/../backend/conexion.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos
    $club_id          = $_SESSION['usuario_id'];
    $numero           = (int)($_POST['numero'] ?? 0);
    $tipo_suelo       = $_POST['tipo_suelo'] ?? '';
    $tipo_pared       = $_POST['tipo_pared'] ?? '';
    $fraccion         = $_POST['fraccion_horaria'] ?? '';
    $precio           = (int)($_POST['precio'] ?? 0);
    // Días y horarios: construir array
    $dias = [];
    foreach (['lunes','martes','miercoles','jueves','viernes','sabado','domingo'] as $dia) {
        if (!empty($_POST['dias'][$dia]['activo'])) {
            $from = $_POST['dias'][$dia]['desde'] ?? '';
            $to   = $_POST['dias'][$dia]['hasta'] ?? '';
            $dias[$dia] = ['desde'=> $from, 'hasta'=> $to];
        }
    }
    $dias_json = json_encode($dias);

    // Imagen
    $imagen = null;
    if (!empty($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error']===UPLOAD_ERR_OK) {
        $imagen = basename($_FILES['imagen']['name']);
        move_uploaded_file(
            $_FILES['imagen']['tmp_name'],
            __DIR__ . '/../assets/uploads/' . $imagen
        );
    }

    // Insertar en DB
    $sql = "INSERT INTO canchas
        (club_id, numero, tipo_suelo, tipo_pared, fraccion_horaria, dias_disponibles, precio, imagen)
        VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Error en preparación de la consulta: ' . htmlspecialchars($conn->error));
        }
    $stmt->bind_param(
        'iissssis',
        $club_id, $numero, $tipo_suelo, $tipo_pared, $fraccion, $dias_json, $precio, $imagen
    );
    if ($stmt->execute()) {
        header('Location: administrar_canchas.php'); exit;
    } else {
        $error = 'Error al crear cancha: ' . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}
function generar_horarios() {
    $inicio = strtotime('06:00');
    $fin    = strtotime('23:00');
    $franja = 90 * 60; // 1h 30m en segundos
    $horarios = [];
    for ($h = $inicio; $h <= $fin; $h += $franja) {
        $horarios[] = date('H:i', $h);
    }
    return $horarios;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agregar Cancha | Rivales</title>
    <link rel="stylesheet" href="../assets/css/add_cancha.css">
    <link rel="shortcut icon" href="../assets/img/icono rivales.png" type="image/x-icon">
</head>

<body>
    <?php include '../templates/header.php'; ?>

    <main class="form-container">
        <h2>Agregar Nueva Cancha</h2>
        <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
        <form action="add_cancha.php" method="POST" enctype="multipart/form-data">
            <label>Cancha N°</label>
            <select name="numero" required>
                <option value="">Seleccionar</option>
                <?php for ($i=1; $i<=20; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>

            <label>Tipo de Suelo</label>
            <select name="tipo_suelo" required>
                <option value="">Seleccionar</option>
                <option value="sintetico">Sintético</option>
                <option value="cemento">Cemento</option>
            </select>

            <label>Tipo de Pared</label>
            <select name="tipo_pared" required>
                <option value="">Seleccionar</option>
                <option value="cemento">Cemento</option>
                <option value="blindex">Blindex</option>
            </select>

            <label>Fracción Horaria</label>
            <select name="fraccion_horaria" required>
                <option value="">Seleccionar</option>
                <option value="01:30"
                    <?= isset($_POST['fraccion_horaria']) && $_POST['fraccion_horaria'] === '01:30' ? 'selected' : '' ?>>
                    1h 30m</option>
            </select>

            <label>Días Disponibles y Horarios</label>
            <div class="days">
                <?php foreach (['lunes','martes','miercoles','jueves','viernes','sabado','domingo'] as $dia): ?>
                <div class="day">
                    <label>
                        <input type="checkbox" name="dias[<?= $dia ?>][activo]" value="1"> <?= ucfirst($dia) ?>
                    </label>
                    <div class="day-hours">
                        <label>Desde:
                            <select name="dias[<?= $dia ?>][desde]" required>
                                <?php foreach (generar_horarios() as $hora): ?>
                                <option value="<?= $hora ?>"><?= $hora ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>Hasta:
                            <select name="dias[<?= $dia ?>][hasta]" required>
                                <?php foreach (generar_horarios() as $hora): ?>
                                <option value="<?= $hora ?>"><?= $hora ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <label>Precio ($)</label>
            <input type="number" name="precio" min="0" step="1" required>

            <label>Imagen de la Cancha</label>
            <input type="file" name="imagen" accept="image/*">

            <button type="submit" class="btn">Guardar</button>
            <a href="administrar_canchas.php" class="btn" style="background:#6c757d;">Cancelar</a>
        </form>
    </main>
    <a href="dashboard.php" class="volver-btn">Volver al Dashboard</a>
    <?php include '../templates/footer.php'; ?>
</body>

</html>