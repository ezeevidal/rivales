<?php
require_once __DIR__ . '/../backend/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_usuario = $_POST['tipo_usuario'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $apellido = $_POST['apellido'] ?? null;
    $edad = $_POST['edad'] ?? null;
    $telefono = $_POST['telefono'] ?? null;
    $direccion = $_POST['direccion'] ?? null;
    $ciudad = $_POST['ciudad'] ?? null;
    $provincia = $_POST['provincia'] ?? null;
    $pais = $_POST['pais'] ?? null;
    $mano_habil = $_POST['mano_habil'] ?? null;
    $posicion = $_POST['posicion'] ?? null;
    $categoria = $_POST['categoria'] ?? null;
    $tipo_juego = $_POST['tipo_juego'] ?? null;
    $nombre_complejo = $_POST['nombre_complejo'] ?? null;
    $ubicacion = $_POST['ubicacion'] ?? null;
    $telefono_complejo = $_POST['telefono_complejo'] ?? null;

    // Horarios: json_encode
    $horarios_disponibles = isset($_POST['horarios']) ? json_encode($_POST['horarios']) : null;

    // Imagen
    $imagen_perfil = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_nombre = basename($_FILES['imagen']['name']);
        $ruta_destino = __DIR__ . '/../assets/uploads/' . $imagen_nombre;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen_perfil = $imagen_nombre;
        }
    }

    // Ahora preparamos el INSERT correcto:
    $stmt = $conn->prepare(
        "INSERT INTO usuarios 
        (tipo_usuario, nombre, apellido, edad, email, password, telefono, direccion, ciudad, provincia, pais, mano_habil, posicion, categoria, tipo_juego, horarios_disponibles, imagen_perfil, nombre_complejo, ubicacion, telefono_complejo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt) {
        $stmt->bind_param(
            "ssssssssssssssssssss",
            $tipo_usuario, $nombre, $apellido, $edad, $email, $password,
            $telefono, $direccion, $ciudad, $provincia, $pais,
            $mano_habil, $posicion, $categoria, $tipo_juego,
            $horarios_disponibles, $imagen_perfil, $nombre_complejo, $ubicacion, $telefono_complejo
        );

        if ($stmt->execute()) {
            header("Location: ../views/dashboard.php");
            exit();
        } else {
            echo "Error al ejecutar la consulta: " . $stmt->error;
        }
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rivales - Registro</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script defer>
        document.addEventListener('DOMContentLoaded', function () {
            const tipoUsuario = document.getElementById('tipo_usuario');
            const jugadorFields = document.getElementById('jugadorFields');
            const proveedorFields = document.getElementById('proveedorFields');

            tipoUsuario.addEventListener('change', function () {
                if (this.value === 'jugador') {
                    jugadorFields.style.display = 'block';
                    proveedorFields.style.display = 'none';
                } else if (this.value === 'proveedor') {
                    jugadorFields.style.display = 'none';
                    proveedorFields.style.display = 'block';
                } else {
                    jugadorFields.style.display = 'none';
                    proveedorFields.style.display = 'none';
                }
            });
        });
    </script>
</head>
<body>
    <header>
        <div class="logo">RIVALES</div>
    </header>

    <main>
        <div class="form-container">
            <h2>Crear Cuenta</h2>
            <form action="register.php" method="POST" id="registerForm" enctype="multipart/form-data">
                <select name="tipo_usuario" id="tipo_usuario" required>
                    <option value="">Selecciona tu tipo de cuenta</option>
                    <option value="jugador">Jugador</option>
                    <option value="proveedor">Complejo de Canchas</option>
                </select>

                <div id="commonFields">
                    <input type="text" name="nombre" placeholder="Nombre completo" required>
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>

                <div id="jugadorFields" class="hidden" style="display: none;">
                    <input type="text" name="apellido" placeholder="Apellido">
                    <input type="number" name="edad" placeholder="Edad">
                    <input type="text" name="telefono" placeholder="Teléfono">
                    <input type="text" name="direccion" placeholder="Dirección">
                    <input type="text" name="ciudad" placeholder="Ciudad">
                    <input type="text" name="provincia" placeholder="Provincia">
                    <input type="text" name="pais" placeholder="País">
                    <select name="mano_habil">
                        <option value="">Mano Hábil</option>
                        <option value="derecha">Derecha</option>
                        <option value="izquierda">Izquierda</option>
                    </select>
                    <select name="posicion">
                        <option value="">Posición</option>
                        <option value="reves">Revés</option>
                        <option value="drive">Drive</option>
                    </select>
                    <select name="categoria">
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
                    <select name="tipo_juego">
                        <option value="">Tipo de Juego</option>
                        <option value="ofensivo">Ofensivo</option>
                        <option value="defensivo">Defensivo</option>
                    </select>

                    <fieldset>
                        <legend>Horarios Disponibles</legend>
                        <?php foreach (["lunes", "martes", "miércoles", "jueves", "viernes", "sábado", "domingo"] as $dia): ?>
                            <label>
                                <input type="checkbox" name="horarios[<?php echo $dia; ?>][activo]"> <?php echo ucfirst($dia); ?>
                            </label>
                            <div>
                                Desde: <input type="time" name="horarios[<?php echo $dia; ?>][desde]">
                                Hasta: <input type="time" name="horarios[<?php echo $dia; ?>][hasta]">
                            </div>
                        <?php endforeach; ?>
                    </fieldset>

                    <label>Imagen de perfil:</label>
                    <input type="file" name="imagen" accept="image/*">
                </div>

                <div id="proveedorFields" class="hidden" style="display: none;">
                    <input type="text" name="nombre_complejo" placeholder="Nombre del Complejo">
                    <input type="text" name="ubicacion" placeholder="Ubicación">
                    <input type="text" name="telefono_complejo" placeholder="Teléfono del Complejo">
                </div>

                <button type="submit">Registrarme</button>
            </form>
            <p class="small-text">¿Ya tienes cuenta? <a href="../index.php">Iniciar sesión</a></p>
        </div>
    </main>

    <footer>
        <p>© 2025 Rivales. Todos los derechos reservados.</p>
    </footer>
</body>
</html>