<?php
require_once '../connection/conexion.php';
require_once '../repositories/ProductoDAO.php';

header('Content-Type: application/json');

    $texto = $_GET['query'] ?? '';

    if (trim($texto) === '') {
        echo json_encode([]);
        exit();
    }

    $dao = new ProductoDAO($conn);
    $productos = $dao->buscarProductosCliente($texto);

    echo json_encode($productos);
?>
