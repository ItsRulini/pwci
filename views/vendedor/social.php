<?php
// Este archivo debe estar en views/cliente/social.php y views/vendedor/social.php
// Ajusta las rutas de los require y de los enlaces de la navbar según la ubicación.
// Por ejemplo, si está en views/cliente/social.php:
require_once '../../models/Usuario.php';
require_once '../../auth/auth.php'; // Para $usuario y requireRole

// $usuario ya está definido en auth.php
// requireRole(['Comprador', 'Vendedor']); // Ambos roles pueden ver esta página

$rolUsuarioActual = $usuario->getRol();
$rutaBase = ''; // Para la navbar
if ($rolUsuarioActual === 'Comprador') {
    $rutaBase = '../cliente/';
} elseif ($rolUsuarioActual === 'Vendedor') {
    $rutaBase = '../vendedor/';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social - Perfiles</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="social.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

     <nav class="navbar">
        <a href="<?php echo $rutaBase; ?>main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>
        
        <?php if ($rolUsuarioActual === 'Comprador'): // Solo el comprador tiene buscador de productos en navbar aquí ?>
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Buscar productos...">
            <span class="search-icon"><i class="fas fa-search"></i></span>
        </div>
        <?php endif; ?>

        <ul class="nav-links">
            <li><a href="<?php echo $rutaBase; ?>perfil.php">Perfil</a></li>
            <?php if ($rolUsuarioActual === 'Comprador'): ?>
                <li><a href="<?php echo $rutaBase; ?>social.php">Social</a></li>
                <li><a href="<?php echo $rutaBase; ?>compra.php">Compras</a></li>
            <?php elseif ($rolUsuarioActual === 'Vendedor'): ?>
                <li><a href="<?php echo $rutaBase; ?>social.php">Social</a></li>
                <li><a href="<?php echo $rutaBase; ?>chat.php">Chat</a></li>
                <li><a href="<?php echo $rutaBase; ?>ventas.php">Ventas</a></li>
            <?php endif; ?>
            
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

    <section id="SocialPage" class="social-page-container">
        <div class="social-header">
            <h2>Descubrir Perfiles</h2>
            <div class="profile-search-container">
                <input type="text" id="profileSearchInput" class="profile-search-bar" placeholder="Buscar por nombre o rol (Comprador/Vendedor)...">
                <span class="profile-search-icon"><i class="fas fa-search"></i></span>
            </div>
        </div>

        <div class="perfiles-container" id="perfilesContainer">
            <!-- {/* */} -->
            <p class="loading-profiles">Cargando perfiles...</p>
        </div>
    </section>

    <?php if ($rolUsuarioActual === 'Comprador'): ?>
        <script src="buscador.js"></script>
    <?php endif; ?>
    <script src="social.js"></script>
</body>
</html>
