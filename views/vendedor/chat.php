<?php
require_once '../../models/Usuario.php'; // Ajusta la ruta si es necesario
require_once '../../auth/auth.php'; // Para la función requireRole y $usuario

// session_start() ya está en auth.php
// $usuario ya está definido en auth.php

// El rol se obtiene de $usuario->getRol()
$rolUsuario = $usuario->getRol();
$idUsuarioActual = $usuario->getIdUsuario();

// Ya no es obligatorio un idChat en la URL, pero si viene, lo usamos para seleccionar el chat activo.
$idChatActivoPorUrl = isset($_GET['idChat']) ? (int)$_GET['idChat'] : 0;

// Determinar la ruta base para enlaces según el rol
$rutaBaseRol = '';
if ($rolUsuario === 'Comprador') {
    $rutaBaseRol = '../cliente/';
} elseif ($rolUsuario === 'Vendedor') {
    $rutaBaseRol = '../vendedor/';
} else {
    // Manejar otros roles o error si es necesario
    // Por ahora, podría redirigir o mostrar un error genérico.
    // Para este ejemplo, asumimos que solo son Comprador o Vendedor.
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Conversaciones</title>

    <link rel="stylesheet" href="../style.css"> <link rel="stylesheet" href="chat.css">   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    
    <nav class="navbar">
        <a href="<?php echo $rutaBaseRol; ?>main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>
        
        <?php if ($rolUsuario === 'Comprador'): ?>
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Buscar productos...">
            <span class="search-icon"><i class="fas fa-search"></i></span>
        </div>
        <?php endif; ?>

        <ul class="nav-links">
            <li><a href="<?php echo $rutaBaseRol; ?>perfil.php">Perfil</a></li>
            <li><a href="<?php echo $rutaBaseRol; ?>chat.php">Chat</a></li>
            <?php if ($rolUsuario === 'Comprador'): ?>
                <li><a href="<?php echo $rutaBaseRol; ?>social.php">Social</a></li>
                <li><a href="<?php echo $rutaBaseRol; ?>compra.php">Compras</a></li>
            <?php elseif ($rolUsuario === 'Vendedor'): ?>
                <li><a href="<?php echo $rutaBaseRol; ?>ventas.php">Ventas</a></li>
            <?php endif; ?>
            <?php if ($rolUsuario === 'Comprador'): ?>
            <li>
                <a href="<?php echo $rutaBaseRol; ?>carrito.php">
                    <i class="fas fa-shopping-cart" style="color: #ffcc00; font-size: 20px;"></i>
                </a>
            </li>
            <?php endif; ?>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <div class="chat-layout">
        <aside id="chats_container_aside">
            <div class="chat-list-header">
                <h2>Conversaciones</h2>
            </div>
            
            <div class="chat-search">
                <input type="text" id="buscarConversacionesInput" placeholder="Buscar conversaciones...">
                <i class="fas fa-search"></i>
            </div>
            
            <ul class="chat-list" id="chatListUl">
                <li class="chat-item-placeholder" style="display: none; text-align: center; padding: 20px; color: #ccc;">Cargando conversaciones...</li>
            </ul>
        </aside>

        <section id="chat_actual_section">
            <div class="chat-header" id="chatHeaderDiv">
                <div class="chat-user-info">
                    <img src="../../multimedia/default/default.jpg" alt="Usuario" id="chatHeaderAvatar">
                    <div>
                        <h3 id="chatHeaderNombreUsuario">Selecciona un chat</h3>
                        <span id="chatHeaderProducto" style="font-size: 0.8em; color: #ccc;"></span>
                    </div>
                </div>
            </div>
            
            <div id="chatMessagesContainer" class="chat-messages">
                <div class="message-placeholder" style="text-align: center; padding: 50px; color: #aaa;">
                    Selecciona una conversación para ver los mensajes.
                </div>
            </div>

            <div id="inputMensajeDiv" class="message-input" style="display: none;"> <input type="text" id="mensajeInput" placeholder="Escribe un mensaje...">
                
                <?php if ($rolUsuario === 'Vendedor'): ?>
                <button title="Hacer una oferta" class="offer-btn" id="offerBtn"><i class="fa-solid fa-envelope"></i></button>
                <?php endif; ?>
                
                <button title="Enviar mensaje" class="send-btn" id="sendBtn"><i class="fas fa-paper-plane"></i></button>
            </div>
        </section>
    </div>

    <?php if ($rolUsuario === 'Vendedor'): ?>
    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="oferta-container" id="ofertaContainerDiv" style="display: none;">
        <div class="oferta-header">
            <h2>Hacer una oferta</h2>
            <button class="close-btn" id="closeOfferPopupBtn"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="oferta-form">
            <label for="ofertaPrecioInput">Precio:</label>
            <input type="number" step="0.01" id="ofertaPrecioInput" name="precio" required>

            <label for="ofertaDescripcionInput">Descripción:</label>
            <textarea id="ofertaDescripcionInput" name="descripcion" rows="4" required></textarea>

            <button type="submit" class="send-btn">Enviar oferta</button>
        </form>
    </div>
    <?php endif; ?>

    <script>
        // Pasar variables PHP a JavaScript de forma segura
        const ID_USUARIO_ACTUAL = <?php echo json_encode($idUsuarioActual); ?>;
        const ROL_USUARIO_ACTUAL = <?php echo json_encode($rolUsuario); ?>;
        const ID_CHAT_ACTIVO_URL = <?php echo json_encode($idChatActivoPorUrl); ?>;
    </script>
    <script src="../chat.js"></script> <?php if ($rolUsuario === 'Comprador'): ?>
        <script src="buscador.js"></script> <?php endif; ?>

</body>
</html>
