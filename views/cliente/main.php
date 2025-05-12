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
    <title>Dashboard</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="main.css">
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

    <!-- Contenedor de productos -->
    <section class="productos">
        
        <!-- Productos Populares -->
        <div id="Populares">
            <h2>Productos Populares</h2>
            <ol id="ListaPopulares">
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Producto 1">
                    <div class="info">
                        <a href="producto.php">Nombre del producto</a>
                        <p>$150 MXN</p>
                        <button>Añadir al carrito</button>
                    </div>
                </li>
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Producto 1">
                    <div class="info">
                        <a href="producto.php">Nombre del producto</a>
                        <p>$150 MXN</p>
                        <button>Añadir al carrito</button>
                    </div>
                </li>
            </ol>
        </div>

        <div id="ParaCotizar">
            <h2>Productos para cotización</h2>
            <ol id="ListaCotizacion">
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Producto 1">
                    <div class="info">
                        <a href="producto.php">Nombre del producto</a>
                        <p>Negociable</p>
                        <button>Enviar mensaje</button>
                    </div>
                </li>
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Producto 1">
                    <div class="info">
                        <a href="producto.php">Nombre del producto</a>
                        <p>Negociable</p>
                        <button>Enviar mensaje</button>
                    </div>
                </li>
                
            </ol>
        </div>
        
        <!-- Productos Recientes -->
        <div id="Recientes">
            <h2>Productos Recientes</h2>
            <ol id="ListaRecientes">
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Producto 2">
                    <div class="info">
                        <a href="producto.php">Nombre del producto</a>
                        <p>$120 MXN</p>
                        <button>Añadir al carrito</button>
                    </div>
                </li>
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Producto 2">
                    <div class="info">
                        <a href="producto.php">Nombre del producto</a>
                        <p>$120 MXN</p>
                        <button>Añadir al carrito</button>
                    </div>
                </li>
            </ol>
        </div>

        <div id="General">
            <h2>Más productos</h2>
            <ol id="ListaProductos">
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Producto 2">
                    <div class="info">
                        <a href="producto.php">Nombre del producto</a>
                        <p>$120 MXN</p>
                        <button>Añadir al carrito</button>
                    </div>
                </li>
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Producto 2">
                    <div class="info">
                        <a href="producto.php">Nombre del producto</a>
                        <p>$120 MXN</p>
                        <button>Añadir al carrito</button>
                    </div>
                </li>
            </ol>
        </div>

    </section>
        
    <script src="main.js"></script>
</body>
</html>
