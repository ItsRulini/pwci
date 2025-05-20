<?php
require_once '../../models/Usuario.php';
require_once '../../auth/auth.php'; // Incluye session_start() y define $usuario

requireRole(['Vendedor']); // Asegurar que solo Vendedores accedan

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
    <title>Perfil de Vendedor</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../administrador/main.css">
    <link rel="stylesheet" href="perfil.css">
    <style>
        .validation-message { display: block; font-size: 0.85em; margin-top: -5px; margin-bottom: 10px; min-height: 1.2em; /* Para evitar saltos de layout */}
        .error { color: #dc3545; }
        .success { color: #28a745; }
        .input-error { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; }
        .input-success { border-color: #28a745 !important; box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important; }
        .form-message { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .success-message-global { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message-global { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        /* Estilo para botón deshabilitado */
        #submitPerfil:disabled { background-color: #6c757d; border-color: #6c757d; cursor: not-allowed; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>
        <ul class="nav-links">
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="social.php">Social</a></li>
            <li><a href="chat.php">Chat</a></li>
            <li><a href="ventas.php">Ventas</a></li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <?php if ($successMessage): ?>
        <div class="form-message success-message-global"><?php echo $successMessage; ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="form-message error-message-global"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <section>
        <div class="infoGeneral">
            <h2>Perfil de Vendedor</h2>

            <form id="formPerfil" action="../../controllers/actualizarUsuario.php" method="POST" enctype="multipart/form-data">
                <label for="email" style="color: whitesmoke">Correo Electrónico:</label>
                <input type="email" id="email" name="email" placeholder="Correo" value="<?php echo htmlspecialchars($usuario->getEmail()); ?>" required>
                <span class="validation-message" id="emailValidationMessage"></span>

                <label for="usuario" style="color: whitesmoke">Nombre de Usuario:</label>
                <input type="text" id="usuario" name="usuario" placeholder="Usuario" value="<?php echo htmlspecialchars($usuario->getNombreUsuario()); ?>" required>
                <span class="validation-message" id="usuarioValidationMessage"></span>

                <label for="password" style="color: whitesmoke">Nueva Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Dejar vacío para no cambiar">
                <span class="validation-message" id="passwordValidationMessage"></span>
                
                <?php
                    $foto = "../../multimedia/default/default.jpg"; 
                    if ($usuario->getFotoAvatar() != null) {
                        $rutaFotoReal = "../../multimedia/imagenPerfil/" . $usuario->getFotoAvatar();
                        if (file_exists($rutaFotoReal)) { 
                            $foto = $rutaFotoReal;
                        }
                    }
                ?>
                <img class="ImageLoaded" src="<?php echo htmlspecialchars($foto); ?>?t=<?php echo time(); ?>" id="profile-image" alt="Avatar">
                <label id="image" for="input-file" class="btn-choose-image">Elige una imagen</label>
                <input type="file" name="avatar" accept="image/*" id="input-file" style="display:none;">
            
                <label for="nombres" style="color: whitesmoke">Nombres:</label>
                <input type="text" id="nombres" name="nombres" placeholder="Nombres" value="<?php echo htmlspecialchars($usuario->getNombres()); ?>" required>
                
                <label for="paterno" style="color: whitesmoke">Apellido Paterno:</label>
                <input type="text" id="paterno" name="paterno" placeholder="Apellido Paterno" value="<?php echo htmlspecialchars($usuario->getPaterno()); ?>" required>

                <label for="materno" style="color: whitesmoke">Apellido Materno:</label>
                <input type="text" id="materno" name="materno" placeholder="Apellido Materno" value="<?php echo htmlspecialchars($usuario->getMaterno()); ?>" required>
                
                <label for="nacimiento" style="color: whitesmoke">Fecha de Nacimiento:</label>
                <input type="date" name="nacimiento" id="nacimiento" value="<?php echo htmlspecialchars($usuario->getFechaNacimiento()); ?>" required>
                <span class="validation-message" id="nacimientoValidationMessage"></span>
            
                <!-- {/* */}
                {/* */} -->
                <input type="hidden" name="privacidad" value="Publico">


                <input type="submit" id="submitPerfil" value="Guardar cambios">
            </form>
        </div>
    </section>

    <div class="pubs-content">
        <section class="pubsection">
            <h2 class="section-title">Solicitudes pendientes</h2>
            <div class="card-container" id="pendientesContainer">
                <!-- {/* */} -->
            </div>
        </section>

        <section class="pubsection">
            <h2 class="section-title">Publicaciones aprobadas</h2>
            <div class="card-container" id="aprobadosContainer">
                <!-- {/* */} -->
            </div>
        </section>

        <section class="pubsection">
            <h2 class="section-title">Solicitudes rechazadas</h2>
            <div class="card-container" id="rechazadosContainer">
                 <!-- {/* */} -->
            </div>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="perfil.js"></script>
</body>
</html>
