<?php
session_start();
require_once __DIR__ . '/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Escapar y limpiar datos
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $edad = intval($_POST['edad']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $ciudad = trim($_POST['ciudad']);
    $provincia = trim($_POST['provincia']);
    $pais = trim($_POST['pais']);
    $mano_habil = trim($_POST['mano_habil']);
    $posicion = trim($_POST['posicion']);
    $categoria = trim($_POST['categoria']);
    $tipo_juego = trim($_POST['tipo_juego']);

    // Horarios seleccionados
    $horarios = [];
    if (!empty($_POST['dias'])) {
        foreach ($_POST['dias'] as $dia) {
            $inicio = $_POST['horario_inicio_' . $dia] ?? null;
            $fin = $_POST['horario_fin_' . $dia] ?? null;
            if ($inicio && $fin) {
                $horarios[$dia] = "$inicio-$fin";
            }
        }
    }
    $horarios_serializados = json_encode($horarios);

    // Procesar imagen
    $imagen_nombre = null;
    if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] == 0) {
        $imagen_tmp = $_FILES['imagen_perfil']['tmp_name'];
        $imagen_nombre = uniqid('perfil_') . '_' . basename($_FILES['imagen_perfil']['name']);
        $ruta_destino = __DIR__ . '/../assets/uploads/' . $imagen_nombre;
        move_uploaded_file($imagen_tmp, $ruta_destino);
    }

    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO jugadores (nombre, apellido, edad, email, telefono, direccion, ciudad, provincia, pais, mano_habil, posicion, categoria, tipo_juego, imagen_perfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssssssssss", $nombre, $apellido, $edad, $email, $telefono, $direccion, $ciudad, $provincia, $pais, $mano_habil, $posicion, $categoria, $tipo_juego, $imagen_nombre);

    if ($stmt->execute()) {
        // Login automático después de registrar
        $_SESSION['usuario_id'] = $stmt->insert_id;
        $_SESSION['usuario_nombre'] = $nombre;
        header("Location: ../views/dashboard.php");
        exit();
    } else {
        echo "Error en el registro: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../pages/register.php");
    exit();
}
?>
