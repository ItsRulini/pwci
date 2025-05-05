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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="main.css">
    <title>Dashboard</title>
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

    <main class="main-content">
        <section class="pubsection">
            <h2 class="section-title">Publicaciones</h2>
            <div class="card-container">

                <div class="card">
                    <img src="../../multimedia/default/default.jpg" alt="Ejemplo de producto" class="card-image">
                    <h3 class="card-title">Nombre del producto</h3>
                    <p class="card-description">Descripción del producto.</p>  
                    <p class="card-price">$100 MXN</p>
                    <button class="card-button-ver-mas">Ver más</button>
                    <button class="card-button-approve"><i class="fas fa-check"></i></button>
                    <button class="card-button-disapprove"><i class="fas fa-times"></i></button>
                </div>
            </div>
        </section>
    </main>

    <script src="main.js"></script>
</body>
</html>