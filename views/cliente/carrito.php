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

    <section class="carrito-container">
        <h2 class="carrito-titulo">Mi Carrito</h2>
        
        <div class="carrito">
            <ul class="contenido-carrito">
                <!-- {/* */} -->
                <li class="producto">
                    <img src="../../multimedia/default/default.jpg" alt="Producto Placeholder">
                    <div class="info">
                        <span>Cargando producto...</span>
                        <p>...</p>
                        <div class="cantidad"><span>1</span></div>
                    </div>
                    <div class="acciones"><button class="eliminar-btn"><i class="fas fa-trash"></i></button></div>
                </li>
            </ul>
            
            <div class="resumen-carrito">
                <div class="linea-resumen">
                    <span>Subtotal</span>
                    <span id="resumenSubtotal">$0.00 MXN</span>
                </div>
                <div class="linea-resumen">
                    <span>Envío</span>
                    <span id="resumenEnvio">$0.00 MXN</span>
                </div>
                <div class="linea-resumen">
                    <span>Impuestos</span>
                    <span id="resumenImpuestos">$0.00 MXN</span>
                </div>
                
                <div class="linea-total">
                    <span>Total</span>
                    <span id="resumenTotal">$0.00 MXN</span>
                </div>
                
                <div class="acciones-carrito">
                    <!-- {/* */} -->
                    <div id="paypal-button-container">
                        <!-- {/* */} -->
                    </div>
                    
                    <!-- {/* */} -->
                    <button class="btn-comprar" id="btnProcederPagoPayPal">
                        Pagar con PayPal <i class="fab fa-paypal"></i>
                    </button>

                    <!-- {/* */} -->
                    <button class="btn-pagar-efectivo" id="btnPagarEfectivo">
                        Pagar en Efectivo <i class="fas fa-money-bill-wave"></i>
                    </button>

                    <button class="clear-btn">
                        Vaciar carrito <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="carrito-vacio" style="display: none;">
            <i class="fas fa-shopping-cart"></i>
            <p>Tu carrito está vacío</p>
            <a href="main.php" class="btn-seguir-comprando">Seguir comprando</a>
        </div>
    </section>

    <!-- {/* */} -->
    <div class="popup-overlay" id="cashPaymentOverlay" style="display: none;"></div>
    <div class="popup-container" id="cashPaymentPopup" style="display: none;">
        <div class="popup-header">
            <h2>Pago en Efectivo</h2>
            <button class="popup-close-btn" id="closeCashPopupBtn">&times;</button>
        </div>
        <div class="popup-body">
            <p>Por favor, realiza tu pago en la tienda de conveniencia más cercana utilizando el siguiente código de referencia:</p>
            <div class="barcode-container" id="barcodeDisplay">
                <!-- {/* */} -->
                Cargando código...
            </div>
            <p>Total a pagar: <strong id="cashTotalAmount"></strong></p>
            <button class="btn-accion-popup" id="btnConfirmarPagoEfectivo">Completar Pago</button>
        </div>
        <div class="popup-footer">
            <p id="cashPaymentMessage" class="payment-message"></p>
        </div>
    </div>

    <script src="https://www.paypal.com/sdk/js?client-id=Adxi5Pt83dliN05vencduR0nFE9NnQtbHJTl6AG7-aaUCJRA63cJjlcFWJxvTcwk45QVZQ-ON0WWXgVw&currency=MXN"></script>
    <script src="carrito.js"></script>
    <script src="buscador.js"></script>
</body>
</html>
