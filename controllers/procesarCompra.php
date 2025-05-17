<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/CarritoDAO.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado o método incorrecto.']);
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$idUsuarioComprador = $usuarioActual->getIdUsuario();

// El idLista del carrito que se va a comprar DEBE estar en la sesión.
if (!isset($_SESSION['idLista']) || empty($_SESSION['idLista'])) {
    echo json_encode(['success' => false, 'message' => 'No se encontró un carrito activo para procesar.']);
    exit();
}
$idListaComprada = $_SESSION['idLista'];

// Opcional: Recibir datos de PayPal si los necesitas para validación o registro adicional
// $paypalOrderID = $_POST['paypalOrderID'] ?? null;
// $paypalPayerID = $_POST['paypalPayerID'] ?? null;
// if (!$paypalOrderID) {
//     echo json_encode(['success' => false, 'message' => 'Faltan datos de la transacción de PayPal.']);
//     exit();
// }

$carritoDAO = new CarritoDAO($conn);
$resultadoProceso = $carritoDAO->procesarCompra($idListaComprada, $idUsuarioComprador);

if (isset($resultadoProceso['status']) && $resultadoProceso['status'] === 'SUCCESS') {
    // Compra procesada exitosamente en la BD.
    // Ahora, crear un nuevo carrito vacío para el usuario y actualizar la sesión.
    $nuevoIdLista = $carritoDAO->crearNuevoCarritoVacioParaUsuario($idUsuarioComprador);
    if ($nuevoIdLista) {
        $_SESSION['idLista'] = $nuevoIdLista; // Actualizar el idLista en la sesión al nuevo carrito vacío
        echo json_encode([
            'success' => true, 
            'message' => $resultadoProceso['message'] . ' Se ha creado un nuevo carrito.',
            'idListaComprada' => $resultadoProceso['idListaComprada'],
            'nuevoIdLista' => $nuevoIdLista
        ]);
    } else {
        // La compra se procesó, pero no se pudo crear un nuevo carrito. Esto es un problema.
        // El usuario podría no poder agregar nuevos ítems hasta que se resuelva.
        error_log("Error crítico: Compra procesada para idLista {$idListaComprada} pero no se pudo crear nuevo carrito para usuario {$idUsuarioComprador}.");
        echo json_encode([
            'success' => true, // La compra en sí fue exitosa en BD
            'message' => $resultadoProceso['message'] . ' ¡Pero hubo un problema al crear tu nuevo carrito! Contacta a soporte.',
            'idListaComprada' => $resultadoProceso['idListaComprada'],
            'error_nuevo_carrito' => true
        ]);
    }
} else {
    // Hubo un error al procesar la compra en la BD (ej. FAIL_CART_INVALID)
    echo json_encode([
        'success' => false, 
        'message' => $resultadoProceso['message'] ?? 'Error desconocido al procesar la compra.'
    ]);
}

$conn->close();
?>
