<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/CarritoDAO.php'; // Contiene agregarProductoAWishlist

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado o método incorrecto.', 'results' => []]);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuario = $usuarioActual->getIdUsuario();

$idProducto = isset($_POST['idProducto']) ? (int)$_POST['idProducto'] : 0;
// Esperamos un array de IDs de lista
$idListasSeleccionadas = isset($_POST['idListas']) && is_array($_POST['idListas']) ? $_POST['idListas'] : [];

if ($idProducto <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de producto no válido.', 'results' => []]);
    exit();
}
if (empty($idListasSeleccionadas)) {
    echo json_encode(['success' => false, 'message' => 'No se seleccionó ninguna wishlist.', 'results' => []]);
    exit();
}

$carritoDAO = new CarritoDAO($conn);
$resultadosPorLista = [];
$todosExitosos = true;
$algunExito = false;

foreach ($idListasSeleccionadas as $idListaStr) {
    $idLista = (int)$idListaStr;
    if ($idLista > 0) {
        $resultado = $carritoDAO->agregarProductoAWishlist($idLista, $idProducto, $idUsuario);
        $resultadosPorLista[$idLista] = $resultado;
        if (!isset($resultado['status']) || strpos($resultado['status'], 'SUCCESS') === false && $resultado['status'] !== 'ALREADY_EXISTS') {
            $todosExitosos = false;
        }
        if (isset($resultado['status']) && (strpos($resultado['status'], 'SUCCESS') !== false || $resultado['status'] === 'ALREADY_EXISTS')) {
            $algunExito = true;
        }
    } else {
        $resultadosPorLista["invalid_id_".$idListaStr] = ['status' => 'FAIL_INVALID_ID', 'message' => 'ID de lista inválido.'];
        $todosExitosos = false;
    }
}

if ($algunExito) { // Si al menos uno fue exitoso o ya existía
    $finalMessage = "Operación completada. Revisa los detalles.";
    if ($todosExitosos && !$algunExito) $finalMessage = "No se realizaron cambios."; // Si todos eran ALREADY_EXISTS o no se seleccionó nada válido
    elseif (!$todosExitosos) $finalMessage = "Algunos productos no se pudieron agregar a todas las listas seleccionadas. Revisa los detalles.";
    
    echo json_encode(['success' => true, 'message' => $finalMessage, 'results' => $resultadosPorLista]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo agregar el producto a ninguna wishlist seleccionada.', 'results' => $resultadosPorLista]);
}

$conn->close();
?>
