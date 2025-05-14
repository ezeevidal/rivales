<?php
session_start();
// Solo clubs pueden ver reservas recibidas
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'club') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';
// Zona horaria
date_default_timezone_set('America/Argentina/Mendoza');

$club_id = $_SESSION['usuario_id'];

// Procesar acciones: confirmar, cancelar, eliminar o eliminar todas canceladas
if (isset($_GET['confirm'])) {
    $res_id = (int)$_GET['confirm'];
    $stmt = $conn->prepare("UPDATE reservas r
        JOIN canchas c ON r.cancha_id=c.id
        SET r.estado='confirmada'
        WHERE r.id=? AND c.club_id=?");
    $stmt->bind_param('ii', $res_id, $club_id);
    $stmt->execute();
    $stmt->close();
    header('Location: reservas_recibidas.php'); exit;
}
if (isset($_GET['cancel'])) {
    $res_id = (int)$_GET['cancel'];
    $stmt = $conn->prepare("UPDATE reservas r
        JOIN canchas c ON r.cancha_id=c.id
        SET r.estado='cancelada'
        WHERE r.id=? AND c.club_id=?");
    $stmt->bind_param('ii', $res_id, $club_id);
    $stmt->execute();
    $stmt->close();
    header('Location: reservas_recibidas.php'); exit;
}
if (isset($_GET['delete'])) {
    $res_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE r FROM reservas r
        JOIN canchas c ON r.cancha_id=c.id
        WHERE r.id=? AND c.club_id=?");
    $stmt->bind_param('ii', $res_id, $club_id);
    $stmt->execute();
    $stmt->close();
    header('Location: reservas_recibidas.php'); exit;
}
// Eliminar todas las reservas canceladas del club
if (isset($_GET['delete_all']) && $_GET['delete_all']==='1') {
    $stmt = $conn->prepare("DELETE r FROM reservas r
        JOIN canchas c ON r.cancha_id=c.id
        WHERE c.club_id=? AND r.estado='cancelada'");
    $stmt->bind_param('i', $club_id);
    $stmt->execute();
    $stmt->close();
    header('Location: reservas_recibidas.php'); exit;
}

// Filtros
$where   = ['c.club_id = ?'];
$params  = [$club_id];
$types   = 'i';

if (!empty($_GET['filter_fecha'])) {
    $where[]   = 'r.fecha = ?';
    $params[]  = $_GET['filter_fecha'];
    $types     .= 's';
}
if (!empty($_GET['filter_cancha'])) {
    $where[]   = 'c.numero = ?';
    $params[]  = (int)$_GET['filter_cancha'];
    $types     .= 'i';
}
if (!empty($_GET['filter_jugador'])) {
    $where[]   = 'u.id = ?';
    $params[]  = (int)$_GET['filter_jugador'];
    $types     .= 'i';
}
$where_sql = implode(' AND ', $where);

// Listas para filtros
$canchas = $conn->query("SELECT DISTINCT c.numero FROM canchas c
    JOIN reservas r ON r.cancha_id=c.id
    WHERE c.club_id={$club_id} ORDER BY c.numero");
$jugadores = $conn->query("SELECT DISTINCT u.id, u.nombre, u.apellido, u.telefono
    FROM usuarios u
    JOIN reservas r ON r.jugador_id=u.id
    JOIN canchas c ON r.cancha_id=c.id
    WHERE c.club_id={$club_id} ORDER BY u.nombre, u.apellido");

// Consulta reservas con teléfono
$sql = "SELECT r.id, r.fecha, r.hora_inicio, r.hora_fin, c.numero, u.nombre, u.apellido, u.telefono, r.estado
        FROM reservas r
        JOIN canchas c ON r.cancha_id=c.id
        JOIN usuarios u ON r.jugador_id=u.id
        WHERE {$where_sql}
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
    <title>Reservas Recibidas | Rivales</title>
    <link rel="stylesheet" href="../assets/css/mis_reservas.css">
    <style>
        .filter-form { display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:1rem; }
        .filter-form div { display:flex; flex-direction:column; }
        .filter-form label { font-weight:bold; margin-bottom:.25rem; }
        .btn { padding:.4rem .8rem; border:none; border-radius:4px; cursor:pointer; }
        .btn-confirm     { background:#27ae60; color:#fff; }
        .btn-cancel      { background:#f39c12; color:#fff; }
        .btn-delete      { background:#e74c3c; color:#fff; }
        .btn-delete-all { background:#c0392b; color:#fff; margin-left:auto; }
        .btn-whatsapp    { background:#25d366; color:#fff; }
        .reservas-table { width:100%; border-collapse:collapse; }
        .reservas-table th, .reservas-table td { padding:.5rem; border:1px solid #ccc; text-align:center; }
    </style>
</head>
<body>
<?php include '../templates/header.php'; ?>
<main class="container">
    <h1>Reservas Recibidas</h1>
    <form method="get" class="filter-form">
        <div><label>Fecha:</label><input type="date" name="filter_fecha" value="<?= htmlspecialchars($_GET['filter_fecha'] ?? '') ?>"></div>
        <div><label>Cancha:</label><select name="filter_cancha"><option value="">Todas</option><?php while($rc=$canchas->fetch_assoc()): ?><option value="<?= $rc['numero'] ?>" <?= (int)($_GET['filter_cancha']??0)===$rc['numero']?'selected':'' ?>>Cancha <?= $rc['numero'] ?></option><?php endwhile; ?></select></div>
        <div><label>Jugador:</label><select name="filter_jugador"><option value="">Todos</option><?php while($rj=$jugadores->fetch_assoc()): ?><option value="<?= $rj['id'] ?>" <?= (int)($_GET['filter_jugador']??0)===$rj['id']?'selected':'' ?>><?= htmlspecialchars($rj['nombre'].' '.$rj['apellido']) ?></option><?php endwhile; ?></select></div>
        <button type="submit" class="btn">Filtrar</button>
        <button type="submit" name="delete_all" value="1" class="btn btn-delete-all" onclick="return confirm('Eliminar todas canceladas?');">Eliminar todas canceladas</button>
    </form>
    <table class="reservas-table">
        <thead>
            <tr><th>Fecha</th><th>Inicio</th><th>Fin</th><th>Cancha</th><th>Jugador</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows===0): ?><tr><td colspan="7">No hay reservas.</td></tr>
            <?php else: while($r=$result->fetch_assoc()): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                    <td><?= htmlspecialchars($r['hora_inicio']) ?></td>
                    <td><?= htmlspecialchars($r['hora_fin']) ?></td>
                    <td><?= htmlspecialchars($r['numero']) ?></td>
                    <td><?= htmlspecialchars($r['nombre'].' '.$r['apellido']) ?></td>
                    <td><?= htmlspecialchars($r['estado']) ?></td>
                    <td>
                        <?php if ($r['estado']!=='cancelada'): ?>
                            <a href="reservas_recibidas.php?confirm=<?= $r['id'] ?>" class="btn btn-confirm">Confirmar</a>
                        <?php endif; ?>
                        <?php if ($r['estado']==='confirmada'): ?>
                            <a href="reservas_recibidas.php?cancel=<?= $r['id'] ?>" class="btn btn-cancel">Cancelar</a>
                        <?php endif; ?>
                        <?php if ($r['estado']!=='confirmada'): ?>
                            <a href="reservas_recibidas.php?delete=<?= $r['id'] ?>" class="btn btn-delete" onclick="return confirm('Eliminar esta reserva?');">Eliminar</a>
                        <?php endif; ?>
                        <?php if (!empty($r['telefono'])): ?>
                            <?php
                                $tel = preg_replace('/\D+/', '', $r['telefono']);
                                $url = 'https://wa.me/54'. $tel . '?text=' . urlencode('Hola '. $r['nombre'] . ', respecto a tu reserva de la cancha '. $r['numero'] .' el '. date('d/m/Y', strtotime($r['fecha'])) .' a las '. $r['hora_inicio']);
                            ?>
                            <a href="<?= $url ?>" target="_blank" class="btn btn-whatsapp">WhatsApp</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</main>
<a href="dashboard.php" class="volver-btn">Volver al Dashboard</a>
<?php include '../templates/footer.php'; ?>
</body>
</html>