<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../models/Producto.php';
require_once '../repositories/ProductoDAO.php';

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode([]);
        exit();
    }

    $query = $_GET['query'] ?? '';
    $categoria = $_GET['categoria'] ?? '';

    $precioMin = (isset($_GET['precioMin']) && $_GET['precioMin'] !== '') ? floatval($_GET['precioMin']) : null;
    $precioMax = (isset($_GET['precioMax']) && $_GET['precioMax'] !== '') ? floatval($_GET['precioMax']) : null;

    $dao = new ProductoDAO($conn);
    $productos = $dao->buscarProductosFiltrados($query, $categoria, $precioMin, $precioMax);

    echo json_encode($productos);
?>
