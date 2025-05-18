<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/CarritoDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado o método incorrecto.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuario = $usuarioActual->getIdUsuario();

$idLista = isset($_POST['idLista']) ? (int)$_POST['idLista'] : 0;
$idProducto = isset($_POST['idProducto']) ? (int)$_POST['idProducto'] : 0;

if ($idLista <= 0 || $idProducto <= 0) {
    echo json_encode(['success' => false, 'message' => 'IDs de wishlist o producto no válidos.']);
    exit();
}

$carritoDAO = new CarritoDAO($conn);
$resultado = $carritoDAO->eliminarProductoDeWishlist($idLista, $idProducto, $idUsuario);

if (isset($resultado['status']) && $resultado['status'] === 'SUCCESS') {
    echo json_encode(['success' => true, 'message' => $resultado['message']]);
} else {
    echo json_encode(['success' => false, 'message' => $resultado['message'] ?? 'Error al eliminar el producto de la wishlist.']);
}

$conn->close();
?>
