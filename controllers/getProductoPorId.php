<?php
require_once '../connection/conexion.php';
require_once '../repositories/ProductoDAO.php';

header('Content-Type: application/json');

$idProducto = isset($_GET['idProducto']) ? (int)$_GET['idProducto'] : 0;

$productoDAO = new ProductoDAO($conn);
$producto = $productoDAO->obtenerProductoPorId($idProducto);

if ($producto) {
    echo json_encode($producto);
} else {
    echo json_encode(["success" => false, "message" => "Producto no encontrado."]);
}
?>
