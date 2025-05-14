<?php
require_once __DIR__ . '/conexion.php';

if (!isset($_GET['cancha_id']) || !isset($_GET['fecha'])) {
    echo json_encode([]);
    exit;
}

$cancha_id = (int)$_GET['cancha_id'];
$fecha = $_GET['fecha'];

// 1. Obtener los datos de la cancha (apertura, cierre y fracción)
$sql = "SELECT horario_apertura, horario_cierre, fraccion_horaria FROM canchas WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $cancha_id);
$stmt->execute();
$stmt->bind_result($apertura, $cierre, $fraccion);
if (!$stmt->fetch()) {
    echo json_encode([]);
    exit;
}
$stmt->close();

// 2. Obtener las reservas existentes en esa fecha
$sql = "SELECT hora_inicio, hora_fin FROM reservas WHERE cancha_id=? AND fecha=? AND estado='confirmada'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $cancha_id, $fecha);
$stmt->execute();
$result = $stmt->get_result();

$reservadas = [];
while ($row = $result->fetch_assoc()) {
    $reservadas[] = ['inicio' => $row['hora_inicio'], 'fin' => $row['hora_fin']];
}
$stmt->close();

// 3. Generar horarios disponibles dinámicamente
function sumarTiempo($hora, $fraccion) {
    $h = new DateTime($hora);
    $f = DateTime::createFromFormat('H:i', $fraccion);
    $h->add(new DateInterval('PT' . $f->format('H') . 'H' . $f->format('i') . 'M'));
    return $h->format('H:i');
}

$horas_disponibles = [];
$hora_actual = $apertura;
while (strtotime($hora_actual) + strtotime($fraccion) - strtotime('00:00') <= strtotime($cierre)) {
    $hora_fin = sumarTiempo($hora_actual, $fraccion);

    // Comprobar que no se superponga con reservas existentes
    $ocupada = false;
    foreach ($reservadas as $res) {
        if (
            ($hora_actual >= $res['inicio'] && $hora_actual < $res['fin']) ||
            ($hora_fin > $res['inicio'] && $hora_fin <= $res['fin']) ||
            ($hora_actual <= $res['inicio'] && $hora_fin >= $res['fin'])
        ) {
            $ocupada = true;
            break;
        }
    }

    if (!$ocupada) {
        $horas_disponibles[] = [
            'inicio' => $hora_actual,
            'fin'    => $hora_fin
        ];
    }

    $hora_actual = $hora_fin;
}

echo json_encode($horas_disponibles);
exit;
?>
