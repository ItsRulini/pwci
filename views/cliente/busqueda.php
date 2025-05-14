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
    <title>Resultados de Búsqueda</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="busqueda.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>
        
        <div class="search-container">
            <input id="buscador" type="text" class="search-bar" placeholder="Buscar productos...">
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

    <main class="contenedor">
        <aside class="filtros">
            <h2>Filtros</h2>
            <form id="filtrosForm">
                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria">
                    <option value="">Todas</option>
                    <option value="ropa">Ropa</option>
                    <option value="electronica">Electrónica</option>
                    <option value="hogar">Hogar</option>
                </select>
    
                <label for="precio">Precio:</label>
                <input type="number" id="precioMin" name="precioMin" placeholder="Mínimo">
                <input type="number" id="precioMax" name="precioMax" placeholder="Máximo">
    
                <button type="submit">Aplicar Filtros</button>
            </form>
    
        </aside>
    
        <section class="productos">
            <h2>Resultados de Búsqueda</h2>
            <ol id="ListaResultados">
                <!-- Los resultados de búsqueda se insertarán aquí dinámicamente -->
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
    
            <div id="noResultados" style="display: none;">
                <p>No se encontraron resultados para tu búsqueda.</p>
            </div>
    
        </section>
    </main>

    <script src="busqueda.js"></script>
    <script src="buscador.js"></script>
</body>
</html>
