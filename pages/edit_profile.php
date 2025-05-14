<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../backend/conexion.php';

$tipo = $_SESSION['tipo_usuario'];   // 'jugador' o 'club'
$id   = $_SESSION['usuario_id'];
$error   = '';
$success = '';

// 1. Recuperar imagen actual si existe
if ($tipo === 'jugador') {
    $stmt = $conn->prepare('SELECT imagen_perfil FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($imagen_perfil);
    $stmt->fetch();
    $stmt->close();
} else {
    $stmt = $conn->prepare('SELECT imagen_complejo FROM clubes WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($imagen_complejo);
    $stmt->fetch();
    $stmt->close();
}

// 2. Inicializar resto de variables
// Jugador
$nombre = $apellido = $email = $telefono = $direccion = $ciudad = $provincia = $pais = $mano_habil = $posicion = $categoria = $tipo_juego = '';
$edad   = '';
// Club
$nombre_encargado = $dni_encargado = $telefono_encargado = $email_encargado = $nombre_complejo = $cuit_complejo = $direccion_complejo = $telefono_complejo = $cantidad_canchas = $tipo_cesped = $tipo_pared = $tipo_techo = $horario_apertura = $horario_cierre = '';

// 3. Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($tipo === 'jugador') {
        // Leer campos jugador
        foreach (['nombre','apellido','edad','email','telefono','direccion','ciudad','provincia','pais','mano_habil','posicion','categoria','tipo_juego'] as $f) {
            $$f = trim($_POST[$f] ?? '');
        }
        // Manejo de imagen de perfil
        if (!empty($_FILES['imagen_perfil']['tmp_name']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
            $imagen_perfil = basename($_FILES['imagen_perfil']['name']);
            move_uploaded_file(
                $_FILES['imagen_perfil']['tmp_name'], __DIR__ . '/../assets/uploads/' . $imagen_perfil
            );
        }
        // Actualizar usuarios
        $sql = "UPDATE usuarios SET
            nombre = ?, apellido = ?, edad = ?, email = ?, telefono = ?, direccion = ?,
            ciudad = ?, provincia = ?, pais = ?, mano_habil = ?, posicion = ?, categoria = ?,
            tipo_juego = ?, imagen_perfil = ?
            WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssisssssssssssi',
            $nombre,
            $apellido,
            $edad,
            $email,
            $telefono,
            $direccion,
            $ciudad,
            $provincia,
            $pais,
            $mano_habil,
            $posicion,
            $categoria,
            $tipo_juego,
            $imagen_perfil,
            $id
        );
        if ($stmt->execute()) {
            $_SESSION['usuario_nombre'] = $nombre;
            $success = '¡Modificado exitosamente! Serás redirigido al dashboard...';
        } else {
            $error = 'Error al actualizar perfil: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        // Leer campos club
        foreach (['nombre_encargado','dni_encargado','telefono_encargado','email_encargado','nombre_complejo','cuit_complejo','direccion_complejo','telefono_complejo','cantidad_canchas','tipo_cesped','tipo_pared','tipo_techo','horario_apertura','horario_cierre'] as $f) {
            $$f = trim($_POST[$f] ?? '');
        }
        // Convertir cantidad a entero
        $cant_can_int = (int) $cantidad_canchas;
        // Manejo de imagen de complejo
        if (!empty($_FILES['imagen_complejo']['tmp_name']) && $_FILES['imagen_complejo']['error'] === UPLOAD_ERR_OK) {
            $imagen_complejo = basename($_FILES['imagen_complejo']['name']);
            move_uploaded_file(
                $_FILES['imagen_complejo']['tmp_name'], __DIR__ . '/../assets/uploads/' . $imagen_complejo
            );
        }
        // Actualizar clubes
        $sql = "UPDATE clubes SET
            nombre_encargado = ?, dni_encargado = ?, telefono_encargado = ?, email_encargado = ?,
            nombre_complejo = ?, cuit_complejo = ?, direccion_complejo = ?, telefono_complejo = ?, cantidad_canchas = ?,
            tipo_cesped = ?, tipo_pared = ?, tipo_techo = ?, horario_apertura = ?, horario_cierre = ?, imagen_complejo = ?
            WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssssssissssssi',
            $nombre_encargado,
            $dni_encargado,
            $telefono_encargado,
            $email_encargado,
            $nombre_complejo,
            $cuit_complejo,
            $direccion_complejo,
            $telefono_complejo,
            $cant_can_int,
            $tipo_cesped,
            $tipo_pared,
            $tipo_techo,
            $horario_apertura,
            $horario_cierre,
            $imagen_complejo,
            $id
        );
        if ($stmt->execute()) {
            $_SESSION['usuario_nombre'] = $nombre_encargado;
            $success = '¡Modificado exitosamente! Serás redirigido al dashboard...';
        } else {
            $error = 'Error al actualizar perfil: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

// 4. Obtener datos actuales para mostrar en el formulario
if ($tipo === 'jugador') {
    $stmt = $conn->prepare(
        'SELECT nombre, apellido, edad, email, telefono, direccion, ciudad, provincia, pais, mano_habil, posicion, categoria, tipo_juego, imagen_perfil FROM usuarios WHERE id = ?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result(
        $nombre,
        $apellido,
        $edad,
        $email,
        $telefono,
        $direccion,
        $ciudad,
        $provincia,
        $pais,
        $mano_habil,
        $posicion,
        $categoria,
        $tipo_juego,
        $imagen_perfil
    );
    $stmt->fetch();
    $stmt->close();
} else {
    $stmt = $conn->prepare(
        'SELECT nombre_encargado, dni_encargado, telefono_encargado, email_encargado, nombre_complejo, cuit_complejo, direccion_complejo, telefono_complejo, cantidad_canchas, tipo_cesped, tipo_pared, tipo_techo, horario_apertura, horario_cierre, imagen_complejo FROM clubes WHERE id = ?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result(
        $nombre,
        $dni,
        $telefono,
        $email,
        $nombre_comp,
        $cuit,
        $direccion,
        $telefono_comp,
        $cantidad_can,
        $cesped,
        $pared,
        $techo,
        $hora_ap,
        $hora_ci,
        $imagen_complejo
    );
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Perfil | Rivales</title>
    <?php if ($success): ?>
    <script>
    setTimeout(() => window.location.href = 'dashboard.php', 2000);
    </script>
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/edit_profile.css">
    <link rel="shortcut icon" href="../assets/img/icono rivales.png" type="image/x-icon">
</head>

<body>
    <?php include '../templates/header.php'; ?>
    <main class="form-container">
        <h2>Editar Perfil</h2><br>
        <?php if ($success): ?><p class="success"><?=htmlspecialchars($success)?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?=htmlspecialchars($error)?></p><?php endif; ?>
        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <?php if ($tipo === 'jugador'): ?>
            <div class="avatar-preview">
                <img src="<?= $imagen_perfil ? '../assets/uploads/' . htmlspecialchars($imagen_perfil) : '../assets/img/default_profile.png' ?>"
                    class="avatar-img" alt="Avatar">
            </div>
            <?php if (!$imagen_perfil): ?><p class="prompt">Por favor, sube tu foto de perfil.</p><?php endif; ?>
            <label>Nombre:</label><input type="text" name="nombre" value="<?=htmlspecialchars($nombre)?>" required>
            <label>Apellido:</label><input type="text" name="apellido" value="<?=htmlspecialchars($apellido)?>" required>
            <label>Edad:</label><input type="number" name="edad" value="<?=htmlspecialchars($edad)?>" required>
            <label>Email:</label><input type="email" name="email" value="<?=htmlspecialchars($email)?>" required>
            <label>Teléfono:</label><input type="text" name="telefono" value="<?=htmlspecialchars($telefono)?>" required>
            <label>Dirección:</label><input type="text" name="direccion" value="<?=htmlspecialchars($direccion)?>" required>
            <label>Ciudad:</label><input type="text" name="ciudad" value="<?=htmlspecialchars($ciudad)?>">
            <label>Provincia:</label><input type="text" name="provincia" value="<?=htmlspecialchars($provincia)?>">
            <label>País:</label><input type="text" name="pais" value="<?=htmlspecialchars($pais)?>">
            <label>Mano Hábil:</label>
            <select name="mano_habil" required>
                <option value="">Seleccionar mano</option>
                <option value="derecha" <?=($mano_habil==='derecha')?'selected':''?>>Derecha</option>
                <option value="izquierda" <?=($mano_habil==='izquierda')?'selected':''?>>Izquierda</option>
            </select>
            <label>Posición:</label>
            <select name="posicion" required>
                <option value="">Seleccionar posición</option>
                <option value="reves" <?=($posicion==='reves')?'selected':''?>>Revés</option>
                <option value="drive" <?=($posicion==='drive')?'selected':''?>>Drive</option>
            </select>
            <label>Categoría:</label>
            <select name="categoria" required>
                <option value="">Seleccionar categoría</option>
                <?php foreach (['1ra','2da','3ra','4ta','5ta','6ta','7ma','8va'] as $cat): ?>
                <option value="<?=$cat?>" <?=($categoria===$cat)?'selected':''?>><?=$cat?></option>
                <?php endforeach; ?>
            </select>
            <label>Tipo de Juego:</label>
            <select name="tipo_juego" required>
                <option value="">Seleccionar tipo</option>
                <option value="ofensivo" <?=($tipo_juego==='ofensivo')?'selected':''?>>Ofensivo</option>
                <option value="defensivo" <?=($tipo_juego==='defensivo')?'selected':''?>>Defensivo</option>
            </select>
            <label>Imagen de Perfil:</label><input type="file" name="imagen_perfil" accept="image/*">
            <?php else: ?>
            <div class="avatar-preview">
                <img src="<?= $imagen_complejo ? '../assets/uploads/' . htmlspecialchars($imagen_complejo) : '../assets/img/default_complex.png' ?>"
                    class="avatar-img" alt="Complejo">
            </div>
            <?php if (!$imagen_complejo): ?><p class="prompt">Por favor, sube la imagen de tu complejo.</p>
            <?php endif; ?>
            <label>Nombre Encargado:</label><input type="text" name="nombre_encargado"
                value="<?=htmlspecialchars($nombre)?>" required>
            <label>DNI Encargado:</label><input type="text" name="dni_encargado" value="<?=htmlspecialchars($dni)?>"
                required>
            <label>Teléfono Encargado:</label><input type="text" name="telefono_encargado"
                value="<?=htmlspecialchars($telefono)?>" required>
            <label>Email Encargado:</label><input type="email" name="email_encargado"
                value="<?=htmlspecialchars($email)?>" required>
            <label>Nombre Complejo:</label><input type="text" name="nombre_complejo"
                value="<?=htmlspecialchars($nombre_comp)?>" required>
            <label>CUIT:</label><input type="text" name="cuit_complejo" value="<?=htmlspecialchars($cuit)?>" required>
            <label>Dirección Complejo:</label><input type="text" name="direccion_complejo"
                value="<?=htmlspecialchars($direccion)?>" required>
            <label>Teléfono Complejo:</label><input type="text" name="telefono_complejo"
                value="<?=htmlspecialchars($telefono_comp)?>" required>
            <label>Cantidad Canchas:</label><input type="number" name="cantidad_canchas"
                value="<?=htmlspecialchars($cantidad_can)?>" required>
            <label>Tipo Césped:</label>
            <select name="tipo_cesped" required>
                <option value="">Seleccione césped</option>
                <option value="sintetico" <?=($cesped==='sintetico')?'selected':''?>>Sintético</option>
                <option value="cemento" <?=($cesped==='cemento')?'selected':''?>>Cemento</option>
            </select>
            <label>Tipo Pared:</label>
            <select name="tipo_pared" required>
                <option value="">Seleccione pared</option>
                <option value="cemento" <?=($pared==='cemento')?'selected':''?>>Cemento</option>
                <option value="blindex" <?=($pared==='blindex')?'selected':''?>>Blindex</option>
            </select>
            <label>Tipo Techo:</label>
            <select name="tipo_techo" required>
                <option value="">Seleccione techo</option>
                <option value="cubierto" <?=($techo==='cubierto')?'selected':''?>>Cubierto</option>
                <option value="descubierto" <?=($techo==='descubierto')?'selected':''?>>Descubierto</option>
            </select>
            <label>Horario Apertura:</label><input type="time" name="horario_apertura"
                value="<?=htmlspecialchars($hora_ap)?>">
            <label>Horario Cierre:</label><input type="time" name="horario_cierre"
                value="<?=htmlspecialchars($hora_ci)?>">
            <label>Imagen Complejo:</label><input type="file" name="imagen_complejo" accept="image/*">
            <?php endif; ?>
            <button type="submit">Guardar Cambios</button>
            <button type="button" onclick="window.location.href='dashboard.php'">Volver al Dashboard</button>
        </form>
    </main>
    <?php include '../templates/footer.php'; ?>
</body>

</html>
