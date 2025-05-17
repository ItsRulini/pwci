<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/TransaccionDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(['success' => false, 'transacciones' => [], 'message' => 'Usuario no autenticado.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuario = $usuarioActual->getIdUsuario();

// Los filtros son opcionales para este controlador, pero si los quieres aplicar
// para que el dropdown de compras a calificar también se filtre, puedes hacerlo.
// Por ahora, el DAO `obtenerTransaccionesParaCalificar` no toma filtros,
// pero podría modificarse si es un requisito.
// $idCategoriaFiltro = isset($_GET['idCategoria']) ? (int)$_GET['idCategoria'] : 0;
// $fechaDesde = isset($_GET['fechaDesde']) ? $_GET['fechaDesde'] : null;
// $fechaHasta = isset($_GET['fechaHasta']) ? $_GET['fechaHasta'] : null;

$transaccionDAO = new TransaccionDAO($conn);
// Asumimos que obtenerTransaccionesParaCalificar solo necesita idUsuario
$transacciones = $transaccionDAO->obtenerTransaccionesParaCalificar($idUsuario);

if ($transacciones !== null) {
    echo json_encode(['success' => true, 'transacciones' => $transacciones]);
} else {
    echo json_encode(['success' => false, 'transacciones' => [], 'message' => 'Error al obtener las transacciones para calificar.']);
}

$conn->close();
?>
