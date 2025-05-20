<?php
// Al inicio de tus archivos perfil.php (ej. views/cliente/perfil.php)
require_once '../../models/Usuario.php'; 
// Asumiendo que auth.php ya incluye session_start() y define $usuario
require_once '../../auth/auth.php'; 

// requireRole(['Comprador']); // O el rol específico para esta vista de perfil

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
    <title>Perfil de <?php echo htmlspecialchars($usuario->getRol()); ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="perfil.css">
    <style>
        .validation-message { display: block; font-size: 0.85em; margin-top: -5px; margin-bottom: 10px; }
        .error { color: #dc3545; }
        .success { color: #28a745; }
        .input-error { border-color: #dc3545 !important; }
        .input-success { border-color: #28a745 !important; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>
        
        <?php if ($usuario->getRol() === 'Comprador'): ?>
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Buscar productos...">
            <span class="search-icon"><i class="fas fa-search"></i></span>
        </div>
        <?php endif; ?>

        <ul class="nav-links">
            <?php if ($usuario->getRol() === 'Comprador'): ?>
                <li><a href="social.php">Social</a></li>
                <li><a href="compra.php">Compras</a></li>
            <?php elseif ($usuario->getRol() === 'Vendedor'): ?>
                <li><a href="ventas.php">Ventas</a></li>
            <?php endif; ?>
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="chat.php">Chat</a></li>
            <?php if ($usuario->getRol() === 'Comprador'): ?>
            <li>
                <a href="carrito.php">
                <i class="fas fa-shopping-cart" style="color: #ffcc00; font-size: 20px;"></i>
                </a>
            </li>
            <?php endif; ?>
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
            <h2>Perfil de <?php echo htmlspecialchars(strtolower($usuario->getRol())); ?></h2>

            <form id="formPerfil" action="../../controllers/actualizarUsuario.php" method="POST" enctype="multipart/form-data">
                
                <label for="email" style="color: whitesmoke">Correo Electrónico:</label>
                <input type="email" id="email" name="email" placeholder="Correo" value="<?php echo htmlspecialchars($usuario->getEmail()); ?>" required>
                <span class="validation-message" id="emailValidationMessage"></span>

                <label for="usuario" style="color: whitesmoke">Nombre de Usuario:</label>
                <input type="text" id="usuario" name="usuario" placeholder="Usuario" value="<?php echo htmlspecialchars($usuario->getNombreUsuario()); ?>" required>
                <span class="validation-message" id="usuarioValidationMessage"></span>

                <label for="password" style="color: whitesmoke">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Nueva contraseña (dejar vacío para no cambiar)" value="">
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
            
                <div class="privacidad">
                    <h3>Privacidad del perfil</h3>
                    <div>
                        <input type="radio" id="privado" name="privacidad" value="Privado" 
                        <?php if ($usuario->getPrivacidad() == "Privado") echo 'checked'; ?>>
                        <label for="privado">Privado</label>

                        <input type="radio" id="publico" name="privacidad" value="Publico"
                        <?php if ($usuario->getPrivacidad() == "Publico") echo 'checked'; ?>>
                        <label for="publico">Público</label>
                    </div>
                </div>

                <input type="submit" id="submitPerfil" value="Guardar cambios">
            </form>
        </div>

        <!-- {/* */} -->
        <?php
            // Ejemplo para cliente/perfil.php
            if (strtolower($usuario->getRol()) === 'cliente' || strtolower($usuario->getRol()) === 'comprador') {
                echo '<div class="wishlists">
                        <div class="infoListas">
                            <h2>Mis Wishlists</h2>
                            <i class="fas fa-plus" title="Agregar lista" id="btnAbrirPopup"></i>
                        </div>
                        <ul class="listas">
                            <li>Cargando wishlists...</li>
                        </ul>
                      </div>';
                // Incluir popups de crear y editar wishlist aquí
                echo '<div id="popup" class="popup">
                          <div class="popup-content">
                              <span class="close" id="btnCerrarPopup">&times;</span>
                              <h3>Crear nueva wishlist</h3>
                              <form id="formWishlist">
                                  <input type="text" id="nombreLista" name="nombreLista" placeholder="Nombre de la lista" required>
                                  <textarea id="descripcion" name="descripcion" placeholder="Descripcion de la lista"></textarea>
                                  <div class="privacidadLista">
                                      <h3>Privacidad: </h3>
                                      <section>
                                          <input id="privada" type="radio" name="listaPrivacidad" value="Privada">
                                          <label for="privada">Privada</label>
                                          <input id="publica" type="radio" name="listaPrivacidad" value="Publica">
                                          <label for="publica">Pública</label>
                                      </section>
                                  </div>
                                  <button id="submitLista" type="submit">Crear Lista</button>
                              </form>
                          </div>
                      </div>

                      <div id="popupEditarLista" class="popup-editar" style="display: none;">
                          <div class="popup-editar-content">
                              <span class="close" id="btnCerrarEditarLista">&times;</span>
                              <h3>Editar Wishlist</h3>
                              <form id="formEditarWishlist">
                                  <label for="editarNombreLista">Nombre:</label>
                                  <input type="text" id="editarNombreLista" name="editarNombreLista" placeholder="Nombre de la lista" required>
                                  <label for="editarDescripcionLista">Descripción:</label>
                                  <textarea id="editarDescripcionLista" name="editarDescripcionLista" placeholder="Descripción de la lista"></textarea>
                                  <div class="privacidadLista">
                                      <h3>Privacidad: </h3>
                                      <section>
                                          <input id="editarPrivadaLista" type="radio" name="editarListaPrivacidad" value="Privada">
                                          <label for="editarPrivadaLista">Privada</label>
                                          <input id="editarPublicaLista" type="radio" name="editarListaPrivacidad" value="Publica">
                                          <label for="editarPublicaLista">Pública</label>
                                      </section>
                                  </div>
                                  <h4>Productos:</h4>
                                  <ul id="listaProductosEditar"></ul>
                                  <button type="submit" id="btnGuardarCambiosWishlist">Guardar Cambios</button>
                              </form>
                          </div>
                      </div>';

            } elseif (strtolower($usuario->getRol()) === 'vendedor') {
                 echo '<div class="pubs-content">
                        <section class="pubsection">
                            <h2 class="section-title">Solicitudes pendientes</h2>
                            <div class="card-container" id="pendientesContainer"></div>
                        </section>
                        <section class="pubsection">
                            <h2 class="section-title">Publicaciones aprobadas</h2>
                            <div class="card-container" id="aprobadosContainer"></div>
                        </section>
                        <section class="pubsection">
                            <h2 class="section-title">Publicaciones rechazadas</h2>
                            <div class="card-container" id="rechazadosContainer"></div>
                        </section>
                       </div>';
            }
            // Añadir más elseif para Admin, SuperAdmin si tienen contenido específico aquí
        ?>
    </section>

    <!-- {/* */} -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="perfil.js"></script>
    <?php if ($usuario->getRol() === 'Comprador'): ?>
        <script src="buscador.js"></script>
    <?php endif; ?>
</body>
</html>
