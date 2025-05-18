<?php
require_once '../../models/Usuario.php'; // Ajusta la ruta si es necesario
require_once '../../auth/auth.php'; // Para $usuario y requireRole

// session_start() ya está en auth.php
// $usuario ya está definido en auth.php
requireRole(['Vendedor']); // Solo Vendedores pueden acceder

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de ventas</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="ventas.css">
</head>
<body>
    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>

        <ul class="nav-links">
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="social.php">Social</a></li>
            <li><a href="chat.php">Chat</a></li>
            <li><a href="ventas.php">Ventas</a></li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <h1>Consulta de ventas</h1>

        <aside class="filtros">
            <h2>Filtros</h2>
            <form id="filtrosForm">
                <label for="categoria">Categoría:</label>
                <select id="categoria" name="idCategoria">
                    <option value="0">Todas</option> 
                    <!-- {/* Las categorías se cargarán aquí por JS */} -->
                </select>

                <label for="fechaDesdeVenta">Fecha de venta (desde):</label>
                <input type="date" id="desdeFechaVenta" name="fechaDesde"> 

                <label for="hastaFechaVenta">Fecha de venta (hasta):</label>
                <input type="date" id="hastaFechaVenta" name="fechaHasta">

                <button type="submit">Aplicar Filtros</button>
            </form>
        </aside>

        <section class="ventas-detallada-section">
            <h2>Consulta detallada</h2>

            <div class="table-content">
                <table class="ventas-table">
                    <thead>
                        <tr>
                            <th>Fecha y hora de venta</th>
                            <th>Categoría(s)</th>
                            <th>Producto</th>
                            <th>Cantidad Vendida</th>
                            <th>Calificación Prom.</th>
                            <th>Precio Venta</th>
                            <th>Existencia Actual</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyVentasDetalladas">
                        <!-- {/* */} -->
                        <tr><td colspan="7" style="text-align:center;">Aplicar filtros para ver resultados.</td></tr>
                    </tbody>
                </table>
            </div>

        </section>

        <section class="venta-agrupada-section">
            <h2>Consulta agrupada</h2>

            <div class="table-content">
                <table class="ventas-table">
                    <thead>
                        <tr>
                            <th>Mes-Año</th>
                            <th>Categoría</th>
                            <th>Unidades Vendidas</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyVentasAgrupadas">
                        <!-- {/* */} -->
                         <tr><td colspan="3" style="text-align:center;">Aplicar filtros para ver resultados.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
    
    <script src="ventas.js"></script> 
</body>
</html>
