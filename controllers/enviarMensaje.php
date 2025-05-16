<?php
require_once '../connection/conexion.php';
require_once '../repositories/ChatDAO.php';
require_once '../models/Usuario.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit();
}

$usuario = $_SESSION['usuario'];
$idRemitente = $usuario->getIdUsuario();
$tipo = $_POST['tipo'] ?? 'texto';
$mensaje = trim($_POST['mensaje'] ?? '');
$idChat = (int) ($_POST['idChat'] ?? 0);
$precioOferta = isset($_POST['precio']) ? floatval($_POST['precio']) : null;

if (!$idChat || $tipo === 'oferta' && $precioOferta === null) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit();
}

$dao = new ChatDAO($conn);

// Insertar mensaje (de texto o oferta)
$idMensaje = $dao->insertarMensaje($tipo, $mensaje, $idRemitente, $idChat);

if (!$idMensaje) {
    echo json_encode(['success' => false, 'message' => 'Error al insertar mensaje.']);
    exit();
}

// Si es oferta, insertar en tabla Oferta
if ($tipo === 'oferta') {
    $dao->insertarOferta($idMensaje, $precioOferta);
}

echo json_encode(['success' => true, 'idMensaje' => $idMensaje]);
?>