<?php
require '../../models/Usuario.php';

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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="perfil.css">
    <link rel="stylesheet" href="main.css">
    <title>Document</title>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="main.html" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>

        <ul class="nav-links">
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="../index.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <section>
        <div class="infoGeneral">

            <h2>Perfil de administrador</h2>

            <form id="formPerfil" action="cambiosPerfil.php" method="POST" enctype="multipart/form-data">
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

    <div class="pubs-content">
        <section class="pubsection">
            <h2 class="section-title">Aprobadas por mí</h2>
            <div class="card-container">

                <div class="card">
                    <img src="../../multimedia/default/default.jpg" alt="Ejemplo de producto" class="card-image">
                    <h3 class="card-title">Nombre del producto</h3>
                    <p class="card-description">Descripción del producto.</p>  
                    <p class="card-price">$100 MXN</p>
                    <button class="card-button-ver-mas">Ver más</button>
                </div>
            </div>
        </section>
    </div>

    <script src="perfil.js"></script>
    
</body>
</html>