<?php
require_once '../../models/Usuario.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php"); // Redirigir al login si no hay sesión
    exit();
}

$usuario = $_SESSION['usuario'];
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="registro.css">
    <title>Registro</title>
</head>

<body>

    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Super Administrador</h1>
        </a>

        <ul class="nav-links">
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="registro.php">Registro</a></li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <section id="registroSection">
        <div class="contenedorForm">
            <h3>Registro</h3>
            <form id="formRegistro" action="../../controllers/registroUsuario.php" method="post" enctype="multipart/form-data">
                <input type="email" id="email" placeholder="correo" name="email" required>
                <div id="email-error" class="error-message"></div>
                <input type="text" id="usuario" placeholder="usuario" name="usuario" required>
                <div id="usuario-error" class="error-message"></div>
                <input type="password" id="password" placeholder="contraseña" name="pass" required>
                <div id="pass-error" class="error-message"></div>
                <img class="ImageLoaded" src="../../multimedia/default/default.jpg" id="profile-image">
                <label for="input-file">Eliga una imagen</label>
                <input type="file" accept="image/*" id="input-file" name="avatar">

                <input type="text" id="nombres" placeholder="Nombres" name="nombres" required>
                <input type="text" id="paterno" placeholder="Apellido paterno" name="paterno" required>
                <input type="text" id="materno" placeholder="Apellido materno" name="materno" required>

                <input type="date" id="nacimiento" name="nacimiento" required>
                
                <div class="rol">
                    <h3>Rol de usuario</h3>

                    <div>
                        <input type="radio" id="admin" name="rol" value="Admin">
                        <label for="admin">Administrador</label>
                    </div>
                    
                </div>

                <div class="sexo">
                    <h3>Género</h3>
                    <div>
                        <input type="radio" id="masculino" name="sexo" value="Masculino">
                        <label for="masculino">Masculino</label>
                
                        <input type="radio" id="femenino" name="sexo" value="Femenino">
                        <label for="femenino">Femenino</label>
                    </div>
                </div>

                <input type="submit" id="submitRegistro" value="Registrarse">
            </form>
        </div>

    </section>

    <script src="registro.js"></script>
</body>

</html>