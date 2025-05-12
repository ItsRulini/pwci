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

    $dao = new CarritoDAO($conn);
    $success = $dao->vaciarCarrito($idLista);

    echo json_encode(['success' => $success]);
?>
