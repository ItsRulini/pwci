<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/TransaccionDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(['success' => false, 'productos' => [], 'message' => 'Usuario no autenticado.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuario = $usuarioActual->getIdUsuario();

$idTransaccion = isset($_GET['idTransaccion']) ? (int)$_GET['idTransaccion'] : 0;

if ($idTransaccion <= 0) {
    echo json_encode(['success' => false, 'productos' => [], 'message' => 'ID de transacción no válido.']);
    exit();
}

$transaccionDAO = new TransaccionDAO($conn);
// El método en el DAO ya valida que la transacción pertenezca al usuario.
$productos = $transaccionDAO->obtenerProductosDeCompraParaCalificar($idTransaccion, $idUsuario);

if ($productos !== null) {
    echo json_encode(['success' => true, 'productos' => $productos]);
} else {
    // Esto podría significar que la transacción no existe, no pertenece al usuario, o hubo un error.
    echo json_encode(['success' => false, 'productos' => [], 'message' => 'Error al obtener los productos de la compra o acceso denegado.']);
}

$conn->close();
?>
