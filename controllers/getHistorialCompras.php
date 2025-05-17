<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/TransaccionDAO.php'; // Usaremos el nuevo DAO

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(['success' => false, 'historial' => [], 'message' => 'Usuario no autenticado.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuario = $usuarioActual->getIdUsuario();

// Recoger filtros del GET request (si los hay)
$idCategoriaFiltro = isset($_GET['idCategoria']) ? (int)$_GET['idCategoria'] : 0; // 0 para todas
$fechaDesde = isset($_GET['fechaDesde']) ? $_GET['fechaDesde'] : null;
$fechaHasta = isset($_GET['fechaHasta']) ? $_GET['fechaHasta'] : null;

$transaccionDAO = new TransaccionDAO($conn);
$historial = $transaccionDAO->obtenerHistorialCompras($idUsuario, $idCategoriaFiltro, $fechaDesde, $fechaHasta);

if ($historial !== null) {
    echo json_encode(['success' => true, 'historial' => $historial]);
} else {
    echo json_encode(['success' => false, 'historial' => [], 'message' => 'Error al obtener el historial de compras.']);
}

$conn->close();
?>
