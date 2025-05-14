<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/index.css">
    <title>Inicio de Sesión</title>
</head>

<body>
    <div class="container">
        <div class="main-split">
            <div class="left-logo">
                <img src="assets/img/icono rivales w.png" alt="Logo Rivales">
            </div>
            <div class="right-form">
                <h2>Iniciar Sesión</h2>
                <?php
                if (isset($_SESSION['login_error'])) {
                    echo "<p style='color:red'>" . $_SESSION['login_error'] . "</p>";
                    unset($_SESSION['login_error']);
                }
                ?>
                <form action="backend/login.php" method="POST">
                    <input type="email" name="email" required placeholder="Correo Electrónico">
                    <input type="password" name="password" required placeholder="Contraseña">
                    <button type="submit" id="button">Ingresar</button>
                </form>
                <a href="pages/tipo_usuario.php">Registrarme</a>
                <a href="olvide_password.php">Olvidé mi contraseña</a>
            </div>
        </div>
    </div>
    
    <?php include('templates/footer.php'); ?>
</body>


</html>
