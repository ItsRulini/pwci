<?php
require_once '../../models/Usuario.php'; // Ajusta la ruta si es necesario

session_start(); 
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php"); 
    exit();
}

$idProductoFromUrl = isset($_GET['idProducto']) ? (int)$_GET['idProducto'] : 0;
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Producto</title>
    
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../style.css">
        <link rel="stylesheet" href="producto.css"> 
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

    <main>
        <section class="producto" id="seccionProductoDetalle"> 
            <ul class="categorias" id="productoCategoriasUl">
                <li class="categoria">Cargando...</li>
            </ul>
            
            <div class="multimedia" id="productoMultimediaDiv">
                <img src="../../multimedia/default/default.jpg" alt="Cargando multimedia...">
            </div>

            <div class="info" id="productoInfoDiv">
                
                <i class="fas fa-ellipsis-v" id="btnAbrirPopupWishlist" title="Agregar a Wishlist"></i>
                
                <h2>Cargando nombre...</h2>
                <p>Cargando precio...</p> 
                <p>Cargando descripción...</p>
                <button id="btnAccionProducto" class="btn-placeholder-accion">Cargando...</button>
            </div>
        </section>

        <section class="calificacion" id="productoCalificacionSection">
            <h2>Calificación Promedio</h2>
            <div class="estrellas" id="productoEstrellasPromedio"> 
                <span>Cargando calificación...</span>
            </div>
        </section>
    </main>

    <div class="comentarios-container">
        <section class="comentarios">
            <h2>Comentarios</h2>
            <ol id="productoListaComentariosOl">
                <li class="comentario">Cargando comentarios...</li>
            </ol>
        </section>
    </div>

    <div class="popup" id="popup"> 
        <div class="popup-content">
            <span class="close" id="btnCerrarPopup">&times;</span> 
            <p>Agregar producto a wishlist</p>
            <ul class="listas" id="popupWishlistListasUl">
                <!-- {/* */} -->
            </ul>
            <button class="agregar" id="popupBtnAgregarWishlist">Agregar</button>
        </div>
    </div>
    
    <script>
        const ID_PRODUCTO_ACTUAL = <?php echo json_encode($idProductoFromUrl); ?>;
    </script>
    <script src="producto.js"></script>
    <script src="buscador.js"></script>
</body>
</html>
