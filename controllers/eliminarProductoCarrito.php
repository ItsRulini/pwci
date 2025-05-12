<?php
require_once '../connection/conexion.php';
require_once '../repositories/CarritoDAO.php';
session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['usuario']) || !isset($_SESSION['idLista'])) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit();
    }

    $idLista = $_SESSION['idLista'];
    $idProducto = $_POST['idProducto'] ?? null;

    if ($idProducto) {
        $dao = new CarritoDAO($conn);
        $success = $dao->eliminarProducto($idLista, $idProducto);
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no especificado']);
    }
?>
