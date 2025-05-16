<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/ChatDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !isset($_GET['idChat'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado o idChat faltante.']);
    exit();
}

$idChat = intval($_GET['idChat']);
$dao = new ChatDAO($conn);

$mensajes = $dao->obtenerMensajesDeChat($idChat);
echo json_encode(['success' => true, 'mensajes' => $mensajes]);
?>