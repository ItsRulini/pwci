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
    <title>Mi carrito</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="carrito.css">
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

    <!-- Contenedor del Carrito -->
    
    <section class="carrito-container">
        <h2 class="carrito-titulo">Mi Carrito</h2>
        
        <!-- Lista de productos en el carrito -->
        
        <div class="carrito">
            <ul class="contenido-carrito">
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Smartphone XYZ Pro">
                    <div class="info">
                        <span>Smartphone XYZ Pro</span>
                        <p>$3,499 MXN</p>
                        <div class="cantidad">
                            <button type="button">-</button>
                            <span>1</span>
                            <button type="button">+</button>
                        </div>
                    </div>
                    <div class="acciones">
                        <button class="eliminar-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </li>
                
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Audífonos Bluetooth">
                    <div class="info">
                        <span>Audífonos Bluetooth</span>
                        <p>$899 MXN</p>
                        <div class="cantidad">
                            <button type="button">-</button>
                            <span>2</span>
                            <button type="button">+</button>
                        </div>
                    </div>
                    <div class="acciones">
                        <button class="eliminar-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </li>
                
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Smartwatch Sport">
                    <div class="info">
                        <span>Smartwatch Sport</span>
                        <p>$2,199 MXN</p>
                        <div class="cantidad">
                            <button type="button">-</button>
                            <span>1</span>
                            <button type="button">+</button>
                        </div>
                    </div>
                    <div class="acciones">
                        <button class="eliminar-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </li>
            </ul>
            
            <div class="resumen-carrito">
                <div class="linea-resumen">
                    <span>Subtotal</span>
                    <span>$7,496 MXN</span>
                </div>
                <div class="linea-resumen">
                    <span>Envío</span>
                    <span>$150 MXN</span>
                </div>
                <div class="linea-resumen">
                    <span>Impuestos</span>
                    <span>$1,224 MXN</span>
                </div>
                
                <div class="linea-total">
                    <span>Total</span>
                    <span>$8,870 MXN</span>
                </div>
                
                <div class="acciones-carrito">
                    <div id="paypal-button-container"></div>

                    <button class="btn-comprar">
                        Proceder al pago <i class="fas fa-arrow-right"></i>
                    </button>
                    <button class="clear-btn">
                        Vaciar carrito <i class="fas fa-trash"></i>
                    </button>
                </div>
                
            </div>
        </div>

        
        
        <!-- Si el carrito está vacío (descomentar y comentar el contenido anterior para mostrar esto)-->
        
        <div class="carrito-vacio" style="display: none;">
            <i class="fas fa-shopping-cart" style="font-size: 50px; color: #ddd; margin-bottom: 20px;"></i>
            <p>Tu carrito está vacío</p>
            <a href="main.php" class="btn-seguir-comprando">Seguir comprando</a>
        </div>
        
    </section>

    <script src="https://www.paypal.com/sdk/js?client-id=Adxi5Pt83dliN05vencduR0nFE9NnQtbHJTl6AG7-aaUCJRA63cJjlcFWJxvTcwk45QVZQ-ON0WWXgVw&currency=MXN"></script>
    <script src="carrito.js"></script>

</body>
</html>