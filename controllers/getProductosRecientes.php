<?php
require_once '../connection/conexion.php';
require_once '../repositories/ProductoDAO.php';

    header('Content-Type: application/json');

    $productoDAO = new ProductoDAO($conn);
    $productos = $productoDAO->getProductosRecientes();

    echo json_encode($productos);
?>
