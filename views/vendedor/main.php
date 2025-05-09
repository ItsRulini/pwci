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

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="main.css">
    <title>Dashboard</title>
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

    <section id="dashboardSection">
        <div class="contenedorForm">
            <h2>Publicar producto</h2>
            <form id="formDashboard" action="" method="post" enctype="multipart/form-data">
                <input type="text" id="producto" placeholder="Nombre del producto" name="producto" required>
                <textarea id="descripcion" name="descripcion" placeholder="Descripción del producto" required></textarea>

                <div class="inputs-files">
                    <label id="image" for="input-file">Selecciona al menos 3 imágenes</label>
                    <input type="file" accept="image/*" id="input-file" name="imagenes[]" multiple>

                    <div id="preview-carousel" class="carousel"></div>

                    <label id="video-label" for="input-video">Selecciona al menos 1 video</label>
                    <input type="file" accept="video/*" id="input-video" name="video">
                </div>

                <div class="cathegory-group">
                    <div class="cathegory-info">
                        <h3>Selecciona al menos una categoría</h3>
                        <div class="add-category-button" id="openCategoryModal" title="Agregar nueva categoría">+</div>
                    </div>

                    <div id="cathegory-carousel" class="carousel"></div>

                    <div id="mensajeNoCategorias" style="color: #ffcc00; text-align: center; margin-top: 10px; display: none;"></div>

                    <select id="categoria" name="categoria[]" multiple>

                    </select>
                </div>
                

                <div class="radio-group">
                    <h3>Tipo de publicación</h3>

                    <div class="radio-options">
                        <input type="radio" id="venta" name="tipo" value="venta">
                        <label for="venta">Venta</label>

                        <input type="radio" id="cotizacion" name="tipo" value="cotizacion">
                        <label for="cotizacion">Cotización</label>
                    </div>
                </div>

                <input type="number" step="0.01" id="precio" name="precio" placeholder="Precio en MXN" style="display:none;">
                <p id="sinPrecio" style="display: none; color: red;">⚠ Debe ingresar una cantidad mayor a cero.</p>
                <input type="number" id="cantidad" name="cantidad" placeholder="Cantidad disponible" style="display:none;">
                <p id="sinStock" style="display: none; color: red;">⚠ No hay disponibilidad del producto.</p>

                <button type="submit">Registrar Producto</button>
            </form>
        </div>
    </section>

    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeCategoryModal">&times;</span>
            <h2>Nueva Categoría</h2>
            <form id="newCategoryForm">
                <input type="text" id="newCategoryName" placeholder="Nombre de la categoría" required>
                <textarea id="newCategoryDescription" placeholder="Descripción" required></textarea>
                <button type="submit">Agregar Categoría</button>
            </form>
        </div>
    </div>
    

    <script src="main.js"></script>
</body>
</html>