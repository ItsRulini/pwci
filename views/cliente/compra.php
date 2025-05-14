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
    <title>Consulta de pedidos</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="compra.css">
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

    <main class="main-content">
        <h1>Consulta de pedidos</h1>
        
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
    
                <label for="fecha">Fecha de compra</label>
                <input type="date" id="desdeFechaCompra" name="desde">
                <input type="date" id="hastaFechaCompra" name="hasta">
    
                <button type="submit">Aplicar Filtros</button>
            </form>
        </aside>

        <section class="compras-section">
            <h2>Mis compras</h2>

            <div class="table-content">
                <table class="compras-table">
                    <thead>
                        <tr>
                            <th>Número de compra</th>
                            <th>Categoría</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Calificación</th>
                        </tr>
                    </thead>
                    <tbody id="compras-list">
                        <!-- Aquí se llenarán los datos de las compras desde el servidor -->
                         
                        <tr>
                            <td>1</td>
                            <td>Tecnología</td>
                            <td>Audifonos</td>
                            <td>$80 MXN</td>
                            <td>4.5</td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </section>

        <section class="calificar">
            <h2>Califica los productos de tu compra</h2>
            <label for="compra">Número de compra</label>
                <select id="compra" name="idCompra">
                    <option value="">Selecciona una compra</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </label>

            <div class="table-content">
                <table class="compras-table">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Calificación</th>
                            <th>Comentario</th>
                        </tr>
                    </thead>
                    <tbody id="compras-list">
                        <!-- Aquí se llenarán los datos de las compras desde el servidor -->
                         
                        <tr>
                            <td>Tecnología</td>
                            <td>Audifonos</td>
                            <td>$80 MXN</td>
                            <td>
                                <div class="estrellas">
                                    <i class="fas fa-star" data-index="1"></i>
                                    <i class="fas fa-star" data-index="2"></i>
                                    <i class="fas fa-star" data-index="3"></i>
                                    <i class="fas fa-star" data-index="4"></i>
                                    <i class="fas fa-star" data-index="5"></i>
                                </div>
                            </td>
                            <td><input type="text" class="comentario" name="comentario" placeholder="Escribe tu comentario"></td>
                        </tr>

                        <tr>
                            <td>Tecnología</td>
                            <td>Teclado</td>
                            <td>$800 MXN</td>
                            <td>
                                <div class="estrellas">
                                    <i class="fas fa-star" data-index="1"></i>
                                    <i class="fas fa-star" data-index="2"></i>
                                    <i class="fas fa-star" data-index="3"></i>
                                    <i class="fas fa-star" data-index="4"></i>
                                    <i class="fas fa-star" data-index="5"></i>
                                </div>
                            </td>
                            <td><input type="text" class="comentario" name="comentario" placeholder="Escribe tu comentario"></td>
                        </tr>
                        
                    </tbody>
                </table>
                <button id="calificar-btn">Calificar</button>
            </div>
        </section>

    </main>
    <script src="compra.js"></script>
    <script src="buscador.js"></script>
</body>
</html>