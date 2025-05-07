<?php
// Al inicio de tus archivos perfil.php (ej. views/cliente/perfil.php)
require_once '../../models/Usuario.php'; // o la ruta correcta

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}
$usuario = $_SESSION['usuario'];

$successMessage = '';
$errorMessage = '';

if (isset($_GET['success'])) {
    $successMessage = htmlspecialchars(urldecode($_GET['success']));
}
if (isset($_GET['error'])) {
    $errorMessage = htmlspecialchars(urldecode($_GET['error']));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="perfil.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>

        <ul class="nav-links">
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="chat.php">Chat</a></li>
            <li><a href="ventas.php">Ventas</a></li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <?php if ($successMessage): ?>
        <div style="color: green; text-align: center; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 15px;">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div style="color: red; text-align: center; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 15px;">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <section>
        <div class="infoGeneral">
            <h2>Perfil de <?php echo strtolower($usuario->getRol()); ?></h2>

            <form id="formPerfil" action="../../controllers/actualizarUsuario.php" method="POST" enctype="multipart/form-data">
                <input type="email" id="email" name="email" placeholder="Correo" value="<?php echo htmlspecialchars($usuario->getEmail()); ?>" required>
                <input type="text" id="usuario" name="usuario" placeholder="Usuario" value="<?php echo htmlspecialchars($usuario->getNombreUsuario()); ?>" required>
                <input type="password" id="password" name="password" placeholder="Contraseña" value="<?php echo htmlspecialchars($usuario->getContraseña());?>" required>
                
                <?php
                $foto = "../../multimedia/default/default.jpg";
                if($usuario->getFotoAvatar() != null) {
                    $foto = "../../multimedia/imagenPerfil/" . $usuario->getFotoAvatar();
                }

                ?>

                <img class="ImageLoaded" src="<?php echo $foto ?>" id="profile-image">
                <label id="image" for="input-file">Elige una imagen</label>
                <input type="file" name="avatar" accept="image/*" id="input-file">
            
                <input type="text" id="nombres" name="nombres" placeholder="Nombres" value="<?php echo htmlspecialchars($usuario->getNombres()); ?>" required>
                <input type="text" id="paterno" name="paterno" placeholder="Apellido Paterno" value="<?php echo htmlspecialchars($usuario->getPaterno()); ?>" required>
                <input type="text" id="materno" name="materno" placeholder="Apellido Materno" value="<?php echo htmlspecialchars($usuario->getMaterno()); ?>" required>
                
                <input type="date" name="nacimiento" id="nacimiento" value="<?php echo htmlspecialchars($usuario->getFechaNacimiento()); ?>" required>

                <input type="submit" id="submitPerfil" value="Guardar cambios">
            </form>
        </div>
    </section>
    <script src="perfil.js"></script>
</body>
</html>