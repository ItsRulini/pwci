<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/CalificacionDAO.php'; // Usaremos el nuevo DAO

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuario = $usuarioActual->getIdUsuario();

// Leer el cuerpo JSON de la solicitud
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (!isset($data['calificaciones']) || !is_array($data['calificaciones'])) {
    echo json_encode(['success' => false, 'message' => 'Datos de calificaciones no proporcionados o en formato incorrecto.']);
    exit();
}

$calificacionesAGuardar = $data['calificaciones'];
$calificacionDAO = new CalificacionDAO($conn);
$errores = [];
$exitos = 0;

foreach ($calificacionesAGuardar as $item) {
    $idProducto = isset($item['idProducto']) ? (int)$item['idProducto'] : 0;
    $calificacion = isset($item['calificacion']) ? (int)$item['calificacion'] : 0;
    $comentario = isset($item['comentario']) ? trim($item['comentario']) : '';
    $idTransaccion = isset($item['idTransaccion']) ? (int)$item['idTransaccion'] : null; // Opcional

    if ($idProducto <= 0) {
        $errores[] = "ID de producto inválido para una de las calificaciones.";
        continue;
    }
    if ($calificacion < 0 || $calificacion > 5) { // 0 es válido si solo hay comentario
        $errores[] = "Calificación inválida para el producto ID {$idProducto}. Debe ser entre 1 y 5 (o 0 si solo hay comentario).";
        continue;
    }
    if ($calificacion === 0 && empty($comentario)) { // Si no hay calificación ni comentario, no guardar nada.
        continue;
    }


    $resultado = $calificacionDAO->guardarCalificacionComentario($idUsuario, $idProducto, $calificacion, $comentario, $idTransaccion);
    if ($resultado['success']) {
        $exitos++;
    } else {
        $errores[] = "Error al guardar para producto ID {$idProducto}: " . $resultado['message'];
    }
}

if ($exitos > 0 && empty($errores)) {
    echo json_encode(['success' => true, 'message' => "{$exitos} calificaciones/comentarios guardados correctamente."]);
} elseif ($exitos > 0 && !empty($errores)) {
    echo json_encode(['success' => true, 'message' => "{$exitos} calificaciones/comentarios guardados. Errores: " . implode("; ", $errores)]);
} elseif (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => "No se pudieron guardar las calificaciones. Errores: " . implode("; ", $errores)]);
} else {
    echo json_encode(['success' => false, 'message' => "No se procesaron calificaciones."]); // Si no se envió nada válido
}

$conn->close();
?>
