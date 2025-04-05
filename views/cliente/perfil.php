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
    <title>Perfil</title>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="perfil.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="main.html" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>
        
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Buscar productos...">
            <span class="search-icon"><i class="fas fa-search"></i></span>
        </div>

        <ul class="nav-links">
            <li><a href="social.html">Social</a></li>
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="chat.html">Chat</a></li>
            <li>
                <a href="carrito.html">
                <i class="fas fa-shopping-cart" style="color: #ffcc00; font-size: 20px;"></i>
                </a>
            </li>
            <li><a href="../index.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <section>
        <div class="infoGeneral">

            <h2>Perfil de cliente</h2>

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
            
                <div class="privacidad">
                    <h3>Privacidad del perfil</h3>
                    <div>
                        <input type="radio" id="privado" name="privacidad" value="Privado" 
                        <?php if ($usuario->getPrivacidad() == "Privado") echo 'checked'; ?>>
                        <label for="privado">Privado</label>

                        <input type="radio" id="publico" name="privacidad" value="Público" 
                        <?php if ($usuario->getPrivacidad() == "Publico") echo 'checked'; ?>>
                        <label for="publico">Público</label>
                    </div>
                </div>

                <input type="submit" id="submitPerfil" value="Guardar cambios">
            </form>

        </div>

        <div class="wishlists">
            <div class="infoListas">
                <h2>Mis Wishlists</h2>
                <i class="fas fa-plus" title="Agregar lista" id="btnAbrirPopup"></i>
            </div>
            
            <ul class="listas">
        
                <li class="lista">
                    <span>Lista de Deseos: Tecnología</span>
                    <i class="fas fa-ellipsis-v"></i>
                    <p>Productos electrónicos que me interesan</p>
                    <ol class="contenidoLista">
                        <li class="producto">
                            <img src="../../multimedia/default/default.jpg" alt="Producto 1">
                            <div class="info">
                                <span>Auriculares Bluetooth</span>
                                <p>$1500 MXN</p>
                            </div>
                        </li>
                        <li class="producto">
                            <img src="../../multimedia/default/default.jpg" alt="Producto 2">
                            <div class="info">
                                <span>Teclado Mecánico</span>
                                <p>$1200 MXN</p>
                            </div>
                        </li>
                    </ol>
                </li>
        
                <li class="lista">
                    <span>Lista de Deseos: Ropa</span>
                    <p>Artículos de moda que quiero comprar</p>
                    <ol class="contenidoLista">
                        <li class="producto">
                            <img src="../../multimedia/default/default.jpg" alt="Producto 3">
                            <div class="info">
                                <span>Sudadera Negra</span>
                                <p>$850 MXN</p>
                            </div>
                        </li>
                    </ol>
                </li>
        
            </ul>
        </div>
        
        <!-- Ventana pop-up -->
        <div id="popup" class="popup">
            <div class="popup-content">
                <span class="close" id="btnCerrarPopup">&times;</span>
                <h3>Crear nueva wishlist</h3>
                <form id="formWishlist">
                    <input type="text" id="nombreLista" name="nombreLista" placeholder="Nombre de la lista" required>

                    <textarea id="descripcion" name="descripcion" placeholder="Descripcion de la lista"></textarea>

                    <div class="privacidadLista">
                        <h3>Privacidad: </h3>
                        <section>
                            <input id="privada" type="radio" name="listaPrivacidad" value="Privado">
                            <label for="privada">Privada</label>

                            <input id="publica" type="radio" name="listaPrivacidad" value="Público">
                            <label for="publica">Pública</label>
                        </section>
                    </div>

                    <button id="submitLista" type="submit">Crear Lista</button>
                </form>
            </div>
        </div>
    </section>

    <script src="perfil.js"></script>
</body>
</html>