<?php
require '../backend/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_usuario = 'jugador';
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $edad = $_POST['edad'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $ciudad = $_POST['ciudad'];
    $provincia = $_POST['provincia'];
    $pais = $_POST['pais'];
    $mano_habil = $_POST['mano_habil'];
    $posicion = $_POST['posicion'];
    $categoria = $_POST['categoria'];
    $tipo_juego = $_POST['tipo_juego'];
    $imagen = $_FILES['imagen']['name'];

    // Subir imagen
    if ($imagen) {
        $ruta_imagen = '../assets/uploads/' . basename($imagen);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_imagen);
    }

    $stmt = $conn->prepare("INSERT INTO usuarios
        (tipo_usuario, nombre, apellido, edad, email, password, telefono, direccion, ciudad, provincia, pais, mano_habil, posicion, categoria, tipo_juego, imagen_perfil)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssssssssssss",
        $tipo_usuario, $nombre, $apellido, $edad, $email, $password, $telefono, $direccion, $ciudad, $provincia, $pais, $mano_habil, $posicion, $categoria, $tipo_juego, $imagen
    );

    if ($stmt->execute()) {
        header("Location: ../pages/dashboard.php");
        exit();
    } else {
        echo "Error al registrar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rivales - Registro Jugador</title>
    <link rel="stylesheet" href="../assets/css/register_jugador.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../assets/img/icono rivales w.png" alt="Logo Rivales"> <!-- Reemplaza la ruta del logo si es necesario -->
        </div>
    </header>

    <main>
        <div class="form-container">
            <h2>Registrar Jugador</h2>

            <form action="register_jugador.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="text" name="apellido" placeholder="Apellido" required>
                <input type="number" name="edad" placeholder="Edad" required>
                <input type="email" name="email" placeholder="Correo electrónico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="text" name="telefono" placeholder="Teléfono" required>
                <input type="text" name="direccion" placeholder="Dirección" required>
                <input type="text" name="ciudad" placeholder="Ciudad" required>
                <input type="text" name="provincia" placeholder="Provincia" required>
                <input type="text" name="pais" placeholder="País" required>

                <select name="mano_habil" required>
                    <option value="">Mano Hábil</option>
                    <option value="derecha">Derecha</option>
                    <option value="izquierda">Izquierda</option>
                </select>
                <select name="posicion" required>
                    <option value="">Posición</option>
                    <option value="reves">Revés</option>
                    <option value="drive">Drive</option>
                </select>
                <select name="categoria" required>
                    <option value="">Categoría</option>
                    <option value="1ra">1ra</option>
                    <option value="2da">2da</option>
                    <option value="3ra">3ra</option>
                    <option value="4ta">4ta</option>
                    <option value="5ta">5ta</option>
                    <option value="6ta">6ta</option>
                    <option value="7ma">7ma</option>
                    <option value="8va">8va</option>
                </select>
                <select name="tipo_juego" required>
                    <option value="">Tipo de Juego</option>
                    <option value="ofensivo">Ofensivo</option>
                    <option value="defensivo">Defensivo</option>
                </select>

                <label>Imagen de perfil:</label>
                <input type="file" name="imagen" accept="image/*" required>

                <button type="submit">Registrar Jugador</button>
            </form>
        </div>
    </main>

    <div style="text-align: center; margin-top: 1rem;">
        <a href="../index.php">Volver al Inicio</a>
    </div>

    <footer>
        <p>© 2025 Rivales. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
