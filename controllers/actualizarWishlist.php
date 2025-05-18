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

// El ID de la lista a editar vendrá en el cuerpo del FormData
$idLista = isset($_POST['idLista']) ? (int)$_POST['idLista'] : 0; 
$nuevoNombre = isset($_POST['editarNombreLista']) ? trim($_POST['editarNombreLista']) : '';
$nuevaDescripcion = isset($_POST['editarDescripcionLista']) ? trim($_POST['editarDescripcionLista']) : '';
$nuevaPrivacidad = isset($_POST['editarListaPrivacidad']) ? $_POST['editarListaPrivacidad'] : '';

if ($idLista <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de wishlist no válido.']);
    exit();
}
if (empty($nuevoNombre)) {
    echo json_encode(['success' => false, 'message' => 'El nombre de la wishlist es obligatorio.']);
    exit();
}
if (!in_array($nuevaPrivacidad, ['Privada', 'Publica'])) {
    echo json_encode(['success' => false, 'message' => 'Valor de privacidad no válido.']);
    exit();
}

$carritoDAO = new CarritoDAO($conn);
$resultado = $carritoDAO->actualizarWishlist($idLista, $idUsuario, $nuevoNombre, $nuevaDescripcion, $nuevaPrivacidad);

if (isset($resultado['status']) && (strpos($resultado['status'], 'SUCCESS') !== false)) {
    // Opcional: Actualizar la sesión si guardabas las wishlists ahí
    // unset($_SESSION['wishlists']); // Forzaría una recarga completa desde la BD la próxima vez
    echo json_encode(['success' => true, 'message' => $resultado['message']]);
} else {
    echo json_encode(['success' => false, 'message' => $resultado['message'] ?? 'Error al actualizar la wishlist.']);
}

$conn->close();
?>
