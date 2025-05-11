<?php
require_once '../../models/Usuario.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$usuario = $_SESSION['usuario'];

if (!isset($_GET['idProducto'])) {
    header("Location: main.php");
    exit();
}

$idProducto = (int) $_GET['idProducto']; // 👈
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

    <!-- Navbar -->
    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>

        <ul class="nav-links">
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <main>
        <section class="producto">
            <!--Categorias a las que pertenece el producto-->
            <ul class="categorias">
                <li class="categoria">Categoria 1</li>
                <li class="categoria">Categoria 2</li>
                <li class="categoria">Categoria 3</li>
                <li class="categoria">Categoria 4</li>
                <li class="categoria">Categoria 5</li>
                <li class="categoria">Categoria 6</li>
            </ul>

            <!--Info general del producto-->
            
            <div class="multimedia">
                <img src="../../multimedia/default/default.jpg" alt="Producto 1">
                <img src="../../multimedia/default/default.jpg" alt="Producto 1">
                <img src="../../multimedia/default/default.jpg" alt="Producto 1">

                <video controls>
                    <source src="../../multimedia/default/video.mp4" type="video/mp4">
                </video>
            </div>

            <div class="info">
                <h2>Nombre del producto</h2>
                <p>$150 MXN</p>
                <p>Descripción del producto</p>

                <div class="buttons">
                    <button class="card-button-approve" onclick="autorizarProducto(<?php echo $idProducto; ?>)"><i class="fas fa-check"></i></button>
                    <button class="card-button-disapprove" onclick="rechazarProducto(<?php echo $idProducto; ?>)"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <div class="info-seller">
                <p>Publicado por: Juanito Pérez</p>
            </div>
        </section>
    </main>
    
    <script src="producto.js"></script>
</body>
</html>