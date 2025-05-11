<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/ProductoDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode([]);
    exit();
}

$usuario = $_SESSION['usuario'];
$idAdministrador = $usuario->getIdUsuario();

$productoDAO = new ProductoDAO($conn);
$productos = $productoDAO->obtenerProductosAprobadosPorAdmin($idAdministrador);

echo json_encode($productos);
?>
