<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/ChatDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(['success' => false, 'conversaciones' => [], 'message' => 'Usuario no autenticado.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuarioActual = $usuarioActual->getIdUsuario();

$chatDAO = new ChatDAO($conn);
$conversaciones = $chatDAO->obtenerConversacionesUsuario($idUsuarioActual);

// Formatear un poco los datos para el frontend si es necesario
foreach ($conversaciones as &$conv) {
    // Formatear fecha del último mensaje
    if (!empty($conv['fechaUltimoMensaje'])) {
        $date = new DateTime($conv['fechaUltimoMensaje']);
        $now = new DateTime();
        $interval = $now->diff($date);

        if ($interval->d == 0) { // Hoy
            $conv['tiempoUltimoMensaje'] = $date->format('H:i');
        } elseif ($interval->d == 1 && $interval->h < 24) { // Ayer
             $conv['tiempoUltimoMensaje'] = 'Ayer';
        } else { // Otra fecha
            $conv['tiempoUltimoMensaje'] = $date->format('d M');
        }
    } else {
        $conv['tiempoUltimoMensaje'] = '';
    }
    // Acortar último mensaje si es muy largo
    if (!empty($conv['ultimoMensaje']) && strlen($conv['ultimoMensaje']) > 30) {
        $conv['ultimoMensaje'] = substr($conv['ultimoMensaje'], 0, 27) . '...';
    } elseif (empty($conv['ultimoMensaje'])) {
        $conv['ultimoMensaje'] = 'Conversación iniciada.';
    }
    // Ruta de imagen por defecto si no hay
    if (empty($conv['fotoOtroUsuario'])) {
        $conv['fotoOtroUsuarioRuta'] = '../../multimedia/default/default.jpg';
    } else {
        $conv['fotoOtroUsuarioRuta'] = '../../multimedia/imagenPerfil/' . $conv['fotoOtroUsuario'];
    }
    if (empty($conv['imagenProducto'])) {
        $conv['imagenProductoRuta'] = '../../multimedia/default/default.jpg';
    } else {
        // Asumiendo que imagenProducto ya es el nombre del archivo, no la ruta completa
        // Si es ruta completa, ajusta esto.
        // La consulta de spObtenerConversacionesUsuario ya devuelve solo el nombre del archivo.
        $conv['imagenProductoRuta'] = '../../multimedia/productos/' . $conv['idProducto'] . '/' . $conv['imagenProducto'];
    }


}
unset($conv); // Romper la referencia del último elemento

echo json_encode(['success' => true, 'conversaciones' => $conversaciones]);

$conn->close();
?>
