<?php
// tipo_usuario.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_usuario = $_POST['tipo_usuario'];

    // Redirigir a la página correspondiente según el tipo de usuario
    if ($tipo_usuario == 'jugador') {
        header("Location: register_jugador.php");
        exit();
    } elseif ($tipo_usuario == 'proveedor') {
        header("Location: register_club.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rivales - Tipo de Usuario</title>
    <link rel="stylesheet" href="../assets/css/tipo_usuario.css">
    <link rel="shortcut icon" href="../assets/img/icono rivales.png" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo"><img src="../assets/img/icono rivales w.png" alt=""></div>
    </header>

    <main>
        <div class="form-container">
            <h2>Selecciona tu tipo de cuenta</h2>
            <form action="tipo_usuario.php" method="POST">
                <button type="submit" name="tipo_usuario" value="jugador">Jugador</button>
                <button type="submit" name="tipo_usuario" value="proveedor">Club</button>
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
