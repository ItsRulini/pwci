<?php
require_once '../../models/Usuario.php'; // Ajusta la ruta si es necesario
require_once '../../auth/auth.php';     // Para $usuario y requireRole

requireRole(['Vendedor']); // Solo Vendedores pueden acceder a esta versión de la página de producto

$idProducto = isset($_GET['idProducto']) ? (int)$_GET['idProducto'] : 0;
if ($idProducto <= 0) {
    header("Location: main.php?error=producto_invalido"); // Redirigir si no hay ID de producto
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Mi Producto</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <!-- {/* */}
    {/* */} -->
    <link rel="stylesheet" href="producto.css">
</head>
<body>

    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>
        <ul class="nav-links">
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="chat.php">Chat</a></li>
            <li><a href="ventas.php">Ventas</a></li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <main>
        <section class="producto-detalle-vendedor">
            <ul class="categorias" id="productoCategorias">
                <!-- {/* */} -->
            </ul>
            
            <div class="multimedia" id="productoMultimedia">
                <!-- {/* */} -->
            </div>

            <div class="info" id="productoInfo">
                <h2 id="productoNombre">Cargando...</h2>
                <p id="productoPrecio"></p>
                <p id="productoDescripcion"></p>
                <p id="productoTipo"></p> 
                
                <div class="stock-management" id="stockManagementSection" style="display: none; margin-top: 15px; padding:10px; border: 1px solid #444; border-radius:5px;">
                    <h4>Gestión de Stock</h4>
                    <p>Stock Actual: <span id="productoStockActual">0</span> unidades</p>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                        <label for="cantidadAAgregarStock" style="margin-bottom:0;">Añadir al stock:</label>
                        <input type="number" id="cantidadAAgregarStock" min="1" value="1" style="width: 80px; padding: 5px;">
                        <button id="btnActualizarStock" style="padding: 6px 10px;">Añadir</button>
                    </div>
                    <p id="stockUpdateMessage" style="font-size:0.9em; margin-top:5px;"></p>
                </div>
            </div>

            <div class="info-seller" id="productoEstadoInfo"> 
                <!-- {/* */} -->
            </div>
        </section>
    </main>
    
    <script>
        const ID_PRODUCTO_VENDEDOR = <?php echo json_encode($idProducto); ?>;
    </script>
    <script src="producto.js"></script>
</body>
</html>
