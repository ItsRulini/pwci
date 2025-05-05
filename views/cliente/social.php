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
    <title>Social</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="social.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

     <!-- Navbar -->
     <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>
        
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Buscar productos...">
            <span class="search-icon"><i class="fas fa-search"></i></span>
        </div>

        <ul class="nav-links">
            <li><a href="social.php">Social</a></li>
            <li><a href="compra.php">Compras</a></li>
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="chat.php">Chat</a></li>
            <li>
                <a href="carrito.php">
                <i class="fas fa-shopping-cart" style="color: #ffcc00; font-size: 20px;"></i>
                </a>
            </li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <section id="Social">
        <h2>Perfiles</h2>
        <div class="perfiles-container">
            <article class="perfil">
                <img src="../../multimedia/default/default.jpg" alt="Perfil 1">
                <div class="info">
                    <span>Usuario 1</span>
                    <p>Descripción del perfil 1</p>
                    <a href="perfil.php" target="_blank">
                        <button>Ir al perfil</button>
                    </a>
                </div>
            </article>
            <article class="perfil">
                <img src="../../multimedia/default/default.jpg" alt="Perfil 2">
                <div class="info">
                    <span>Usuario 2</span>
                    <p>Descripción del perfil 2</p>
                    <a href="perfil.php" target="_blank">
                        <button>Ir al perfil</button>
                    </a>
                </div>
            </article>
            <article class="perfil">
                <img src="../../multimedia/default/default.jpg" alt="Perfil 3">
                <div class="info">
                    <span>Usuario 3</span>
                    <p>Descripción del perfil 3</p>
                    <a href="perfil.php" target="_blank">
                        <button>Ir al perfil</button>
                    </a>
                    
                </div>
            </article>
            <article class="perfil">
                <img src="../../multimedia/default/default.jpg" alt="Perfil 4">
                <div class="info">
                    <span>Usuario 4</span>
                    <p>Descripción del perfil 4</p>
                    <a href="perfil.php" target="_blank">
                        <button>Ir al perfil</button>
                    </a>
                </div>
            </article>
        </div>
    </section>

</body>
</html>