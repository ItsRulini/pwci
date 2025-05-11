<?php
require_once '../connection/conexion.php';
require_once '../repositories/ProductoDAO.php';

    header('Content-Type: application/json');

    $idProducto = isset($_GET['idProducto']) ? (int)$_GET['idProducto'] : 0;

    $productoDAO = new ProductoDAO($conn);
    $multimedia = $productoDAO->obtenerMultimediaPorProducto($idProducto);

    echo json_encode($multimedia);
?>
