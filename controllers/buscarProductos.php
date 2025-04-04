<?php
require_once '../connection/conexion.php';
require_once '../models/Producto.php';
require_once '../repositories/ProductoDAO.php';


if($_SERVER["REQUEST_METHOD"] === "GET"){
    $nombre = $_GET["query"];
    // $productoDAO = new ProductoDAO($conn);
    // $productos = $productoDAO->buscarProducto($nombre);
    $productos = "producto encontrado";
    echo json_encode($productos);
}

?>