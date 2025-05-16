<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/ChatDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado o método incorrecto.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idRemitente = $usuarioActual->getIdUsuario();

$idChat = isset($_POST['idChat']) ? (int)$_POST['idChat'] : 0;
$mensajeTexto = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

if ($idChat <= 0 || empty($mensajeTexto)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para enviar el mensaje.']);
    exit();
}

// VALIDACIÓN ADICIONAL: Verificar que el remitente participa en este chat
$stmtVerif = $conn->prepare("SELECT COUNT(*) AS count FROM Chat_Usuario WHERE idChat = ? AND idUsuario = ?");
$stmtVerif->bind_param("ii", $idChat, $idRemitente);
$stmtVerif->execute();
$resultVerif = $stmtVerif->get_result();
$rowVerif = $resultVerif->fetch_assoc();
$stmtVerif->close();
while ($conn->more_results() && $conn->next_result()) { if ($res = $conn->store_result()) { $res->free(); }}

if ($rowVerif['count'] == 0) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para enviar mensajes a este chat.']);
    exit();
}


$chatDAO = new ChatDAO($conn);
$idMensaje = $chatDAO->insertarMensaje('texto', $mensajeTexto, $idRemitente, $idChat);

if ($idMensaje) {
    echo json_encode(['success' => true, 'message' => 'Mensaje enviado.', 'idMensaje' => $idMensaje]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al enviar el mensaje.']);
}

$conn->close();
?>
