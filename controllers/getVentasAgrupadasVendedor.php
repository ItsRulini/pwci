<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/TransaccionDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SESSION['usuario']->getRol() !== 'Vendedor') {
    echo json_encode(['success' => false, 'ventas' => [], 'message' => 'Acceso denegado.']);
    exit();
}

$vendedor = $_SESSION['usuario'];
$idVendedor = $vendedor->getIdUsuario();

$idCategoriaFiltro = isset($_GET['idCategoria']) ? $_GET['idCategoria'] : 0;
$fechaDesde = isset($_GET['fechaDesde']) ? $_GET['fechaDesde'] : null;
$fechaHasta = isset($_GET['fechaHasta']) ? $_GET['fechaHasta'] : null;

$transaccionDAO = new TransaccionDAO($conn);
$ventas = $transaccionDAO->obtenerVentasAgrupadasVendedor($idVendedor, $idCategoriaFiltro, $fechaDesde, $fechaHasta);

if ($ventas !== null) {
    echo json_encode(['success' => true, 'ventas' => $ventas]);
} else {
    echo json_encode(['success' => false, 'ventas' => [], 'message' => 'Error al obtener las ventas agrupadas.']);
}

$conn->close();
?>
