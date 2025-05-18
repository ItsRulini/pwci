<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/CarritoDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(['success' => false, 'productos' => [], 'message' => 'Usuario no autenticado.']);
    exit();
}

$idUsuario = $_SESSION['usuario']->getIdUsuario();
$idLista = isset($_GET['idLista']) ? (int)$_GET['idLista'] : 0;

if ($idLista <= 0) {
    echo json_encode(['success' => false, 'productos' => [], 'message' => 'ID de lista no válido.']);
    exit();
}

$carritoDAO = new CarritoDAO($conn);
// Pasamos idUsuario para que el SP pueda validar la propiedad de la lista
$productos = $carritoDAO->getProductosDeLista($idLista, $idUsuario);

// El DAO ya maneja el caso de error del SP devolviendo un array vacío o con mensaje de error.
// Si $productos está vacío y no hubo error SQL, significa que la lista está vacía o no es válida.
if (!empty($productos) && isset($productos[0]['idProducto']) && $productos[0]['idProducto'] === null) {
     // El SP devolvió la fila de error
    echo json_encode(['success' => false, 'productos' => [], 'message' => $productos[0]['nombre']]);
} else {
    echo json_encode(['success' => true, 'productos' => $productos]);
}


$conn->close();
?>
