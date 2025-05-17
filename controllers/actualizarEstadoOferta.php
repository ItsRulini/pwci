<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/ChatDAO.php';
require_once '../repositories/ProductoDAO.php'; // Para obtener idProducto si es necesario

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado o método incorrecto.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuarioAccion = $usuarioActual->getIdUsuario();

$idOferta = isset($_POST['idOferta']) ? (int)$_POST['idOferta'] : 0; // Este es idMensaje de la oferta
$accion = isset($_POST['accion']) ? $_POST['accion'] : ''; // 'aceptar', 'rechazar', 'cancelar'

if ($idOferta <= 0 || !in_array($accion, ['aceptar', 'rechazar', 'cancelar'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o acción no válida.']);
    exit();
}

$nuevoEstado = '';
switch ($accion) {
    case 'aceptar':
        $nuevoEstado = 'aceptada';
        break;
    case 'rechazar':
        $nuevoEstado = 'rechazada';
        break;
    case 'cancelar':
        $nuevoEstado = 'eliminada'; // 'eliminada' es el estado para cancelación por vendedor
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción desconocida.']);
        exit();
}

$chatDAO = new ChatDAO($conn);
$resultadoActualizacion = $chatDAO->actualizarEstadoOferta($idOferta, $nuevoEstado, $idUsuarioAccion);

if (isset($resultadoActualizacion['status']) && $resultadoActualizacion['status'] === 'SUCCESS') {
    if ($nuevoEstado === 'aceptada') {
        // Obtener idProducto y precioOferta para agregar al carrito
        $stmtInfoOferta = $conn->prepare(
            "SELECT C.idProducto, O.precio 
             FROM Oferta O
             INNER JOIN Mensaje M ON O.idMensaje = M.idMensaje
             INNER JOIN Chat C ON M.idChat = C.idChat
             WHERE O.idOferta = ?"
        );
        $stmtInfoOferta->bind_param("i", $idOferta);
        $stmtInfoOferta->execute();
        $resultInfoOferta = $stmtInfoOferta->get_result();
        $infoOferta = $resultInfoOferta->fetch_assoc();
        $stmtInfoOferta->close();
        while ($conn->more_results() && $conn->next_result()) { if ($res = $conn->store_result()) { $res->free(); }}

        if ($infoOferta) {
            $idProducto = $infoOferta['idProducto'];
            $precioOferta = $infoOferta['precio'];
            
            // El comprador es $idUsuarioAccion en este caso
            $resultadoCarrito = $chatDAO->agregarOfertaAceptadaAlCarrito($idUsuarioAccion, $idProducto, $idOferta, $precioOferta);
            if (isset($resultadoCarrito['status']) && strpos($resultadoCarrito['status'], 'SUCCESS') !== false) {
                 echo json_encode(['success' => true, 'message' => 'Oferta aceptada y agregada al carrito.', 'carrito_message' => $resultadoCarrito['message']]);
            } else {
                // La oferta se aceptó pero hubo un problema al agregar al carrito
                echo json_encode(['success' => true, 'message' => 'Oferta aceptada, pero hubo un problema al agregarla al carrito: ' . ($resultadoCarrito['message'] ?? 'Error desconocido.'), 'carrito_status' => $resultadoCarrito['status'] ?? 'FAIL_UNKNOWN']);
            }
        } else {
            echo json_encode(['success' => true, 'message' => 'Oferta aceptada, pero no se pudo obtener información para agregar al carrito.']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => $resultadoActualizacion['message']]);
    }
} else {
    echo json_encode(['success' => false, 'message' => $resultadoActualizacion['message'] ?? 'Error al actualizar estado de la oferta.']);
}

$conn->close();
?>
