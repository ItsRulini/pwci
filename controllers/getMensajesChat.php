<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/ChatDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(['success' => false, 'mensajes' => [], 'message' => 'Usuario no autenticado.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuarioActual = $usuarioActual->getIdUsuario();

$idChat = isset($_GET['idChat']) ? (int)$_GET['idChat'] : 0;

if ($idChat <= 0) {
    echo json_encode(['success' => false, 'mensajes' => [], 'message' => 'ID de chat no válido.']);
    exit();
}

// VALIDACIÓN ADICIONAL: Verificar que el usuario actual participa en este chat
$stmtVerif = $conn->prepare("SELECT COUNT(*) AS count FROM Chat_Usuario WHERE idChat = ? AND idUsuario = ?");
$stmtVerif->bind_param("ii", $idChat, $idUsuarioActual);
$stmtVerif->execute();
$resultVerif = $stmtVerif->get_result();
$rowVerif = $resultVerif->fetch_assoc();
$stmtVerif->close();
while ($conn->more_results() && $conn->next_result()) { if ($res = $conn->store_result()) { $res->free(); }}


if ($rowVerif['count'] == 0) {
    echo json_encode(['success' => false, 'mensajes' => [], 'message' => 'Acceso denegado a este chat.']);
    exit();
}


$chatDAO = new ChatDAO($conn);
// Pasamos idUsuarioActual para que el DAO pueda determinar 'esMio'
$resultado = $chatDAO->obtenerMensajesDeChat($idChat, $idUsuarioActual); 

echo json_encode(['success' => true, 'mensajes' => $resultado['mensajes'], 'idUsuarioActual' => $resultado['idUsuarioActual']]);

$conn->close();
?>
