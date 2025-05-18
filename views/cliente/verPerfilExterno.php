    <?php
    // Este archivo debe estar en views/cliente/verPerfilExterno.php 
    // Y una copia similar en views/vendedor/verPerfilExterno.php
    // Ajusta las rutas de los require y de los enlaces de la navbar según la ubicación.
    
    require_once '../../models/Usuario.php';
    require_once '../../auth/auth.php'; // Para $usuario (el que está viendo) y requireRole

    // $usuario ya está definido en auth.php
    // requireRole(['Comprador', 'Vendedor']); // Ambos roles pueden ver esta página

    $rolUsuarioActual = $usuario->getRol(); // Rol del que está viendo la página
    $rutaBase = ''; 
    if ($rolUsuarioActual === 'Comprador') {
        $rutaBase = '../cliente/';
    } elseif ($rolUsuarioActual === 'Vendedor') {
        $rutaBase = '../vendedor/';
    }

    // Obtener el ID del usuario cuyo perfil se va a mostrar
    $idPerfilConsultado = isset($_GET['idUsuario']) ? (int)$_GET['idUsuario'] : 0;

    if ($idPerfilConsultado <= 0) {
        // Manejar error, redirigir o mostrar mensaje
        // header("Location: " . $rutaBase . "social.php?error=perfil_invalido");
        // exit();
        // Por ahora, el JS manejará un mensaje si el backend no devuelve datos.
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Perfil de Usuario</title> {/* El JS lo actualizará */}

        <link rel="stylesheet" href="../style.css"> {/* Estilo general */}
        {/* Necesitaremos un nuevo CSS para esta página */}
        <link rel="stylesheet" href="verPerfilExterno.css"> 
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        {/* Si usas los mismos estilos de tarjeta de producto que en main.php del cliente, inclúyelos */}
        <?php if ($rolUsuarioActual === 'Comprador' || $rolUsuarioActual === 'Vendedor'): ?>
            <link rel="stylesheet" href="<?php echo $rutaBase; ?>main.css"> 
        <?php endif; ?>


    </head>
    <body>

        <nav class="navbar">
            <a href="<?php echo $rutaBase; ?>main.php" class="logo-link">
                <h1 class="logo">Papu Tienda</h1>
            </a>
            
            <?php if ($rolUsuarioActual === 'Comprador'): ?>
            <div class="search-container">
                <input type="text" class="search-bar" placeholder="Buscar productos...">
                <span class="search-icon"><i class="fas fa-search"></i></span>
            </div>
            <?php endif; ?>

            <ul class="nav-links">
                <?php if ($rolUsuarioActual === 'Comprador'): ?>
                    <li><a href="<?php echo $rutaBase; ?>social.php">Social</a></li>
                    <li><a href="<?php echo $rutaBase; ?>compra.php">Compras</a></li>
                <?php elseif ($rolUsuarioActual === 'Vendedor'): ?>
                    <li><a href="<?php echo $rutaBase; ?>social.php">Social</a></li> 
                    <li><a href="<?php echo $rutaBase; ?>ventas.php">Ventas</a></li>
                <?php endif; ?>
                <li><a href="<?php echo $rutaBase; ?>perfil.php">Perfil</a></li>
                <li><a href="<?php echo $rutaBase; ?>chat.php">Chat</a></li>
                
                <?php if ($rolUsuarioActual === 'Comprador'): ?>
                <li>
                    <a href="<?php echo $rutaBase; ?>carrito.php">
                    <i class="fas fa-shopping-cart" style="color: #ffcc00; font-size: 20px;"></i>
                    </a>
                </li>
                <?php endif; ?>
                <li><a href="../logout.php">Cerrar sesión</a></li>
            </ul>
        </nav>

        <main class="perfil-externo-container" id="perfilExternoContainer">
            {/* */}
            <p class="loading-message">Cargando perfil...</p>
        </main>
        
        <script>
            const ID_PERFIL_CONSULTADO = <?php echo json_encode($idPerfilConsultado); ?>;
            // Podrías pasar también el ID_USUARIO_LOGUEADO si el JS lo necesita para alguna lógica
            // const ID_USUARIO_LOGUEADO = <?php echo json_encode($usuario->getIdUsuario()); ?>;
        </script>
        <script src="verPerfilExterno.js"></script> {/* Nuevo JS para esta página */}
        <?php if ($rolUsuarioActual === 'Comprador'): ?>
            <script src="<?php echo $rutaBase; ?>buscador.js"></script>
        <?php endif; ?>

    </body>
    </html>
    