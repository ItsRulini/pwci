<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php'; // Para instanceof y getIdUsuario
require_once '../repositories/UsuarioDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(['success' => false, 'perfiles' => [], 'message' => 'Usuario no autenticado.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuarioActual = $usuarioActual->getIdUsuario();

$usuarioDAO = new UsuarioDAO($conn);
$perfiles = $usuarioDAO->obtenerPerfilesSocial($idUsuarioActual);

if ($perfiles !== null) {
    echo json_encode(['success' => true, 'perfiles' => $perfiles]);
} else {
    echo json_encode(['success' => false, 'perfiles' => [], 'message' => 'Error al obtener los perfiles.']);
}

$conn->close();
?>
