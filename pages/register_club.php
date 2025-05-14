<?php
require_once __DIR__ . '/../backend/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del encargado
    $nombre_encargado   = trim($_POST['nombre_encargado']);
    $dni_encargado      = trim($_POST['dni_encargado']);
    $telefono_encargado = trim($_POST['telefono_encargado']);
    $email_encargado    = trim($_POST['email_encargado']);

    // Contraseña y confirmación
    $password           = $_POST['password'] ?? '';
    $password_confirm   = $_POST['password_confirm'] ?? '';
    if ($password === '' || $password !== $password_confirm) {
        die('<p style="color:red; text-align:center;">Las contraseñas no coinciden o están vacías. <a href="javascript:history.back()">Volver</a></p>');
    }
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // Datos del complejo
    $nombre_complejo    = trim($_POST['nombre_complejo']);
    $cuit_complejo      = trim($_POST['cuit_complejo']);
    $direccion_complejo = trim($_POST['direccion_complejo']);
    $telefono_complejo  = trim($_POST['telefono_complejo']);
    $cantidad_canchas   = (int) $_POST['cantidad_canchas'];

    // Características
    $tipo_cesped = $_POST['tipo_cesped'];
    $tipo_pared  = $_POST['tipo_pared'];
    $tipo_techo  = $_POST['tipo_techo'];

    // Horarios
    $horario_apertura = $_POST['horario_apertura'];
    $horario_cierre   = $_POST['horario_cierre'];

    // Imagen
    $imagen_nombre = null;
    if (!empty($_FILES['imagen_complejo']['tmp_name']) && $_FILES['imagen_complejo']['error'] === UPLOAD_ERR_OK) {
        $imagen_nombre = basename($_FILES['imagen_complejo']['name']);
        move_uploaded_file(
            $_FILES['imagen_complejo']['tmp_name'], 
            __DIR__ . '/../assets/uploads/' . $imagen_nombre
        );
    }

    // Consulta preparada
    $sql = "INSERT INTO `clubes` (
        `nombre_encargado`,`dni_encargado`,`telefono_encargado`,`email_encargado`,
        `nombre_complejo`,`cuit_complejo`,`direccion_complejo`,`telefono_complejo`,`cantidad_canchas`,
        `tipo_cesped`,`tipo_pared`,`tipo_techo`,
        `horario_apertura`,`horario_cierre`,`imagen_complejo`,`password`
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('<p style="color:red; text-align:center;">Error en la preparación de la consulta: ' . htmlspecialchars($conn->error) . '</p>');
    }

    // Tipos: 8 strings, 1 int, 7 strings
    $types = 'ssssssssisssssss';
    $stmt->bind_param(
        $types,
        $nombre_encargado,
        $dni_encargado,
        $telefono_encargado,
        $email_encargado,
        $nombre_complejo,
        $cuit_complejo,
        $direccion_complejo,
        $telefono_complejo,
        $cantidad_canchas,
        $tipo_cesped,
        $tipo_pared,
        $tipo_techo,
        $horario_apertura,
        $horario_cierre,
        $imagen_nombre,
        $password_hashed
    );

    if ($stmt->execute()) {
        header('Location: ../pages/dashboard.php');
        exit;
    } else {
        echo '<p style="color:red; text-align:center;">Error al registrar el club: ' . htmlspecialchars($stmt->error) . '</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Complejo</title>
    <link rel="stylesheet" href="../assets/css/register_club.css">
    <link rel="shortcut icon" href="../assets/img/icono rivales.png" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../assets/img/icono rivales w.png" alt="Logo Rivales">
        </div>
    </header>

    <main>
        <div class="form-container">
            <h2>Registro del Complejo</h2>
            <form action="../pages/register_club.php" method="POST" enctype="multipart/form-data">
                <h4>Datos del Encargado</h4>
                <input type="text" name="nombre_encargado" placeholder="Nombre del Encargado" required>
                <input type="text" name="dni_encargado" placeholder="DNI" required>
                <input type="text" name="telefono_encargado" placeholder="Teléfono" required>
                <input type="email" name="email_encargado" placeholder="Email" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="password" name="password_confirm" placeholder="Confirmar Contraseña" required>

                <h4>Datos del Complejo</h4>
                <input type="text" name="nombre_complejo" placeholder="Nombre del Complejo" required>
                <input type="text" name="cuit_complejo" placeholder="CUIT" required>
                <input type="text" name="direccion_complejo" placeholder="Dirección" required>
                <input type="text" name="telefono_complejo" placeholder="Teléfono del Complejo" required>
                <input type="number" name="cantidad_canchas" placeholder="Cantidad de Canchas" required>

                <h4>Características del Complejo</h4>
                <select name="tipo_cesped" required>
                    <option value="">Tipo de Césped</option>
                    <option value="sintetico">Sintético</option>
                    <option value="cemento">Cemento</option>
                </select>
                <select name="tipo_pared" required>
                    <option value="">Tipo de Pared</option>
                    <option value="cemento">Cemento</option>
                    <option value="blindex">Blindex</option>
                </select>
                <select name="tipo_techo" required>
                    <option value="">Tipo de Techo</option>
                    <option value="cubierto">Cubierto</option>
                    <option value="descubierto">Descubierto</option>
                </select>

                <h4>Horarios</h4>
                <label for="horario_apertura">Horario de Apertura:</label>
                <input type="time" name="horario_apertura" required>

                <label for="horario_cierre">Horario de Cierre:</label>
                <input type="time" name="horario_cierre" required>

                <label for="imagen_complejo">Imagen del Complejo:</label>
                <input type="file" name="imagen_complejo" accept="image/*">

                <button type="submit">Registrarme</button>
            </form>
        </div>
    </main>

    <div style="text-align:center;">
        <a href="../index.php" class="back-link">Volver al Inicio</a>
    </div>

    <footer>
        <p>© 2025 Rivales. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
