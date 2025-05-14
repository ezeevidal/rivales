<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['tipo_usuario'], ['jugador', 'club'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

// Obtener filtros
$filtros = [
    'mano_habil' => $_GET['mano_habil'] ?? '',
    'posicion' => $_GET['posicion'] ?? '',
    'categoria' => $_GET['categoria'] ?? '',
    'tipo_juego' => $_GET['tipo_juego'] ?? ''
];

$where = ['id != ?', "tipo_usuario = 'jugador'"];
$params = [$usuario_id];
$types = 'i';

foreach ($filtros as $campo => $valor) {
    if (!empty($valor)) {
        $where[] = "$campo = ?";
        $params[] = $valor;
        $types .= 's';
    }
}

$sql = "SELECT id, nombre, apellido, categoria, posicion, tipo_juego, mano_habil, imagen_perfil FROM usuarios WHERE ".implode(' AND ', $where);
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();

// Obtener reservas según tipo de usuario
$reservas = [];
if ($tipo_usuario === 'jugador') {
    $stmt_res = $conn->prepare("SELECT r.id, c.numero, cl.nombre_complejo, r.fecha, r.hora_inicio FROM reservas r
        JOIN canchas c ON r.cancha_id = c.id
        JOIN clubes cl ON c.club_id = cl.id
        WHERE r.jugador_id = ? AND r.estado = 'confirmada' AND r.fecha >= CURDATE()
        ORDER BY r.fecha, r.hora_inicio");
    $stmt_res->bind_param('i', $usuario_id);
} else {
    $stmt_res = $conn->prepare("SELECT r.id, c.numero, cl.nombre_complejo, r.fecha, r.hora_inicio FROM reservas r
        JOIN canchas c ON r.cancha_id = c.id
        JOIN clubes cl ON c.club_id = cl.id
        WHERE c.club_id = ? AND r.estado = 'confirmada' AND r.fecha >= CURDATE()
        ORDER BY r.fecha, r.hora_inicio");
    $stmt_res->bind_param('i', $usuario_id);
}
$stmt_res->execute();
$res = $stmt_res->get_result();
while ($r = $res->fetch_assoc()) {
    $reservas[] = $r;
}
$stmt_res->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Rivales o Compañeros</title>
    <link rel="stylesheet" href="../assets/css/rivales.css">
    <link rel="shortcut icon" href="../assets/img/icono rivales.png" type="image/x-icon">
    <style>
        .form-filter { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; }
        .form-filter label { display: flex; flex-direction: column; }
        .player-card { border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; background: #fff; display: flex; align-items: center; justify-content: space-between; }
        .player-info { display: flex; align-items: center; gap: 1rem; }
        .player-info img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
        .invite-form { display: flex; align-items: center; gap: 0.5rem; }
    </style>
</head>
<body>
<?php include '../templates/header.php'; ?>
<main class="container">
    <h1>Buscar Rivales o Compañeros</h1>
    <form method="get" class="form-filter">
        <label>Mano Hábil:
            <select name="mano_habil">
                <option value="">Todas</option>
                <option value="derecha" <?= $filtros['mano_habil']==='derecha'?'selected':'' ?>>Derecha</option>
                <option value="izquierda" <?= $filtros['mano_habil']==='izquierda'?'selected':'' ?>>Izquierda</option>
            </select>
        </label>
        <label>Posición:
            <select name="posicion">
                <option value="">Todas</option>
                <option value="reves" <?= $filtros['posicion']==='reves'?'selected':'' ?>>Revés</option>
                <option value="drive" <?= $filtros['posicion']==='drive'?'selected':'' ?>>Drive</option>
            </select>
        </label>
        <label>Categoría:
            <select name="categoria">
                <option value="">Todas</option>
                <?php foreach (['1ra','2da','3ra','4ta','5ta','6ta','7ma','8va'] as $cat): ?>
                    <option value="<?= $cat ?>" <?= $filtros['categoria']===$cat?'selected':'' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Tipo de Juego:
            <select name="tipo_juego">
                <option value="">Todos</option>
                <option value="ofensivo" <?= $filtros['tipo_juego']==='ofensivo'?'selected':'' ?>>Ofensivo</option>
                <option value="defensivo" <?= $filtros['tipo_juego']==='defensivo'?'selected':'' ?>>Defensivo</option>
            </select>
        </label>
        <button type="submit">Aplicar Filtros</button>
    </form>

    <?php if ($resultado->num_rows === 0): ?>
        <p>No se encontraron jugadores con esos filtros.</p>
    <?php else: while ($jugador = $resultado->fetch_assoc()): ?>
        <div class="player-card">
            <div class="player-info">
                <img src="<?= $jugador['imagen_perfil'] ? '../assets/uploads/' . htmlspecialchars($jugador['imagen_perfil']) : '../assets/img/default_profile.png' ?>" alt="avatar">
                <div>
                    <strong><?= htmlspecialchars($jugador['nombre'].' '.$jugador['apellido']) ?></strong><br>
                    Categoría: <?= htmlspecialchars($jugador['categoria']) ?><br>
                    Posición: <?= htmlspecialchars($jugador['posicion']) ?> - Juego: <?= htmlspecialchars($jugador['tipo_juego']) ?>
                </div>
            </div>
            <form action="enviar_invitacion.php" method="POST" class="invite-form">
                <input type="hidden" name="receptor_id" value="<?= $jugador['id'] ?>">
                <select name="reserva_id" required>
                    <option value="">Seleccionar reserva...</option>
                    <?php foreach ($reservas as $r): ?>
                        <option value="<?= $r['id'] ?>">
                            <?= date('d/m/Y', strtotime($r['fecha'])) ?> - <?= $r['hora_inicio'] ?> - <?= $r['nombre_complejo'] ?> (Cancha <?= $r['numero'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Invitar</button>
            </form>
        </div>
    <?php endwhile; endif; ?>
</main>
<?php include '../templates/footer.php'; ?>
</body>
</html>
