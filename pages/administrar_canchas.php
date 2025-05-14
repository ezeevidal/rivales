<?php
session_start();
// Verificar que el usuario esté logueado y sea club
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'club') {
    header('Location: ../index.php'); exit;
}
require_once __DIR__ . '/../backend/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrar Canchas | Rivales</title>
    <link rel="stylesheet" href="../assets/css/administrar_canchas.css">
</head>

<body>
    <?php include '../templates/header.php'; ?>
    <main class="container">
        <h1>Administrar Canchas</h1>
        <div class="cards">

            <!-- Card para agregar cancha -->
            <div class="card">
                <a href="add_cancha.php" class="btn"><br><br>
                    <img src="../assets/img/add.png" alt="Agregar Cancha">
                    <h3 style="text-decoration: none;">Agregar Cancha</h3>
                    <p>Agrega tu cancha para empezar a reservarla.</p>
                    <div class="card-actions">
                    </div>
                </a>
            </div>

            <?php
            // Listar las canchas existentes
            $clubId = intval($_SESSION['usuario_id']);
            $sql = "SELECT id, numero, tipo_suelo, tipo_pared, fraccion_horaria, precio, imagen
                    FROM canchas
                    WHERE club_id = ?
                    ORDER BY numero ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $clubId);
            $stmt->execute();
            $stmt->bind_result($id, $numero, $suelo, $pared, $fraccion, $precio, $imagen);
            while ($stmt->fetch()):
            ?>
            <div class="card_ex">
                <?php if ($imagen): ?>
                <img src="../assets/uploads/<?= htmlspecialchars($imagen) ?>"
                    alt="Cancha <?= htmlspecialchars($numero) ?>" style="width: 100%; height: 50%; object-fit: cover;">
                <?php else: ?>
                <img src="../assets/img/default_complex.png" alt="Cancha <?= htmlspecialchars($numero) ?>"
                    style="width: 100%; height: 50%; object-fit: cover;">
                <?php endif; ?>
                <h3>Cancha <?= htmlspecialchars($numero) ?></h3>
                <p>Suelo: <?= htmlspecialchars($suelo) ?></p>
                <p>Pared: <?= htmlspecialchars($pared) ?></p>
                <p>Turno: <?= htmlspecialchars(substr($fraccion, 0, 5)) ?>hs.</p>
                <p>Precio: $<?= htmlspecialchars($precio) ?></p>
                <div class="card-actions_ex">
                    <a href="edit_cancha.php?id=<?= htmlspecialchars($id) ?>" class="btn">Editar</a>
                    <a href="delete_cancha.php?id=<?= htmlspecialchars($id) ?>" class="btn_del"
                        onclick="return confirm('¿Eliminar cancha <?= htmlspecialchars($numero) ?>?');" style="color:red; font-weight:400;font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;">Eliminar</a>
                </div>
            </div>
            <?php endwhile;
            $stmt->close();
            ?>
        </div>
    </main>
    <button type="button" onclick="window.location.href='dashboard.php'">Volver al Dashboard</button>
    <?php include '../templates/footer.php'; ?>
</body>
</html>