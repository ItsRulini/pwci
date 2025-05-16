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

// Solo los vendedores pueden enviar ofertas
if ($usuarioActual->getRol() !== 'Vendedor') {
    echo json_encode(['success' => false, 'message' => 'Solo los vendedores pueden enviar ofertas.']);
    exit();
}

$idChat = isset($_POST['idChat']) ? (int)$_POST['idChat'] : 0;
$precioOferta = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
$descripcionOferta = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

if ($idChat <= 0 || $precioOferta <= 0 || empty($descripcionOferta)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para enviar la oferta.']);
    exit();
}

// VALIDACIÓN ADICIONAL: Verificar que el remitente (vendedor) participa en este chat
$stmtVerif = $conn->prepare("SELECT COUNT(*) AS count FROM Chat_Usuario WHERE idChat = ? AND idUsuario = ?");
$stmtVerif->bind_param("ii", $idChat, $idRemitente);
$stmtVerif->execute();
$resultVerif = $stmtVerif->get_result();
$rowVerif = $resultVerif->fetch_assoc();
$stmtVerif->close();
while ($conn->more_results() && $conn->next_result()) { if ($res = $conn->store_result()) { $res->free(); }}


if ($rowVerif['count'] == 0) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para enviar ofertas a este chat.']);
    exit();
}


$chatDAO = new ChatDAO($conn);
// 1. Insertar el mensaje de tipo 'oferta'
$idMensajeOferta = $chatDAO->insertarMensaje('oferta', $descripcionOferta, $idRemitente, $idChat);

if ($idMensajeOferta) {
    // 2. Insertar los detalles de la oferta asociados a ese mensaje
    $ofertaInsertada = $chatDAO->insertarOferta($idMensajeOferta, $precioOferta);
    if ($ofertaInsertada) {
        echo json_encode(['success' => true, 'message' => 'Oferta enviada.', 'idMensajeOferta' => $idMensajeOferta]);
    } else {
        // Aquí podrías considerar eliminar el mensaje si la oferta no se pudo registrar.
        echo json_encode(['success' => false, 'message' => 'Error al registrar los detalles de la oferta.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear el mensaje para la oferta.']);
}

$conn->close();
?>
