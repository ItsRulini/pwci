<?php
require_once '../connection/conexion.php';
require_once '../repositories/ChatDAO.php'; 
require_once '../models/Usuario.php';

session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté autenticado y sea un objeto Usuario
if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit();
}

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuarioActual = $usuarioActual->getIdUsuario();
$idProducto = isset($_POST['idProducto']) ? (int)$_POST['idProducto'] : 0;

// Validar ID del producto
if ($idProducto <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de producto no válido.']);
    exit();
}

$chatDAO = new ChatDAO($conn);

// 1. Obtener el ID del vendedor del producto
$idVendedor = null;
$stmtVendedor = $conn->prepare("SELECT idVendedor FROM Producto WHERE idProducto = ?");
if (!$stmtVendedor) {
    error_log("iniciarChat.php - Error al preparar consulta de vendedor: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor (vendedor).']);
    exit();
}
$stmtVendedor->bind_param("i", $idProducto);
if (!$stmtVendedor->execute()) {
    error_log("iniciarChat.php - Error al ejecutar consulta de vendedor: " . $stmtVendedor->error);
    $stmtVendedor->close();
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor (vendedor exec).']);
    exit();
}
$resultVendedor = $stmtVendedor->get_result();
if ($rowVendedor = $resultVendedor->fetch_assoc()) {
    $idVendedor = (int)$rowVendedor['idVendedor'];
}
$stmtVendedor->close();
// Limpiar resultados de esta consulta SELECT
while ($conn->more_results() && $conn->next_result()) {
    if ($res = $conn->store_result()) {
        $res->free();
    }
}

if (!$idVendedor) {
    echo json_encode(['success' => false, 'message' => 'No se pudo encontrar el vendedor del producto.']);
    exit();
}

// Verificar que el comprador no sea el mismo que el vendedor
if ($idUsuarioActual === $idVendedor) {
    echo json_encode(['success' => false, 'message' => 'No puedes iniciar un chat sobre tu propio producto.']);
    exit();
}

// 2. Buscar si ya existe un chat entre el usuario actual (comprador) y el vendedor para este producto
// La función buscarChatExistente en el DAO ahora maneja la lógica de encontrar el chat entre comprador y vendedor.
$idChat = $chatDAO->buscarChatExistente($idUsuarioActual, $idProducto);

if (!$idChat) { // Si no existe un chat previo, crearlo
    $idChat = $chatDAO->crearChat($idProducto); 

    if (!$idChat) {
        error_log("iniciarChat.php - No se pudo crear el chat para producto ID: " . $idProducto);
        echo json_encode(['success' => false, 'message' => 'No se pudo crear el chat. Intenta de nuevo.']);
        exit();
    }

    // Agregar al usuario actual (comprador) al nuevo chat
    if (!$chatDAO->agregarUsuarioAlChat($idChat, $idUsuarioActual)) {
         error_log("iniciarChat.php - Error al agregar comprador ID $idUsuarioActual al chat ID $idChat");
         // Considerar si se debe eliminar el chat recién creado o notificar un error parcial.
    }
    
    // Agregar al vendedor al nuevo chat
    if (!$chatDAO->agregarUsuarioAlChat($idChat, $idVendedor)) {
        error_log("iniciarChat.php - Error al agregar vendedor ID $idVendedor al chat ID $idChat");
    }

} else {
    // Si el chat ya existe, nos aseguramos de que ambos usuarios estén (INSERT IGNORE lo maneja)
    $chatDAO->agregarUsuarioAlChat($idChat, $idUsuarioActual); // No debería fallar si ya existe
    $chatDAO->agregarUsuarioAlChat($idChat, $idVendedor);    // No debería fallar si ya existe
}

if ($idChat) {
    echo json_encode(['success' => true, 'idChat' => $idChat]);
} else {
    // Este caso no debería ocurrir si la lógica anterior es correcta, pero es un fallback.
    echo json_encode(['success' => false, 'message' => 'No se pudo obtener o crear el ID del chat.']);
}

$conn->close(); // Cerrar la conexión al final
?>
