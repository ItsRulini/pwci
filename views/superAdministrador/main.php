<?php
    require_once '../../models/Usuario.php';

    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: ../index.php"); // Redirigir al login si no hay sesión
        exit();
    }
    
    $usuario = $_SESSION['usuario'];
    $usuarios = $_SESSION['usuarios']; // Obtener la lista de usuarios desde la sesión
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="main.css">
</head>
<body>

    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Super Administrador</h1>
        </a>

        <ul class="nav-links">
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="registro.html">Registro</a></li>
            <li><a href="../index.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <main class="main-content">

        <section class="usuarios">
            <h2>Usuarios en el sistema</h2>

            <table class="usuarios-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre de usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Fecha de registro</th>
                    </tr>
                </thead>
                <tbody id="usuarios-list">
                    
                    <!-- Aquí se llenarán los datos de los usuarios desde el servidor -->
                    <?php foreach ($usuarios as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item->getIdUsuario()); ?></td>
                            <td><?php echo htmlspecialchars($item->getNombreUsuario()); ?></td>
                            <td><?php echo htmlspecialchars($item->getEmail()); ?></td>
                            <td><?php echo htmlspecialchars($item->getRol()); ?></td>
                            <td><?php echo htmlspecialchars($item->getFechaRegistro()); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                </tbody>
            </table>

        </section>

    </main>

</body>
</html>