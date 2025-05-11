<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/ProductoDAO.php';

    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['usuario'])) {
        echo json_encode(["success" => false, "message" => "No autorizado"]);
        exit();
    }

    $usuario = $_SESSION['usuario'];
    $idAdministrador = $usuario->getIdUsuario();

    $idProducto = $_POST['idProducto'] ?? 0;

    $productoDAO = new ProductoDAO($conn);

    if ($productoDAO->rechazarProducto($idProducto, $idAdministrador)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al rechazar producto."]);
    }
?>
