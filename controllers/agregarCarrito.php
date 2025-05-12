<?php
require_once '../connection/conexion.php';
require_once '../repositories/CarritoDAO.php';
require_once '../models/Usuario.php';
session_start();

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(["success" => false, "message" => "Usuario no autenticado."]);
            exit();
        }

        $usuario = $_SESSION['usuario'];
        $idUsuario = $usuario->getIdUsuario();

        $idProducto = isset($_POST['idProducto']) ? (int)$_POST['idProducto'] : 0;

        if ($idProducto <= 0) {
            echo json_encode(["success" => false, "message" => "ID de producto inválido."]);
            exit();
        }

        $carritoDAO = new CarritoDAO($conn);
        $resultado = $carritoDAO->agregarProductoAlCarrito($idUsuario, $idProducto);

        echo json_encode($resultado);
    } else {
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
    }
?>
