<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php'; // Asegúrate que la ruta es correcta

session_start();
header('Content-Type: application/json');

// Validar sesión
if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) { // Verifica que sea un objeto Usuario
    echo json_encode([]); // Devuelve un array vacío si no está autenticado o idLista no está
    exit();
}

// Obtener el idLista de la sesión
// Es crucial que idLista se establezca correctamente en la sesión durante el login o al crear un carrito.
if (!isset($_SESSION['idLista'])) {
    // Intentar obtener/crear el idLista si no existe en la sesión
    // Esto es un fallback, idealmente idLista ya debería estar en la sesión.
    $usuarioActual = $_SESSION['usuario'];
    $idUsuario = $usuarioActual->getIdUsuario();
    
    $stmtLista = $conn->prepare("SELECT idLista FROM Lista WHERE idUsuario = ? AND tipo = 'Carrito' AND estatusLista = TRUE LIMIT 1");
    if ($stmtLista) {
        $stmtLista->bind_param("i", $idUsuario);
        $stmtLista->execute();
        $resultLista = $stmtLista->get_result();
        if ($rowLista = $resultLista->fetch_assoc()) {
            $_SESSION['idLista'] = $rowLista['idLista'];
        }
        $stmtLista->close();
        while ($conn->more_results() && $conn->next_result()) { if ($res = $conn->store_result()) { $res->free(); }}
    }

    if (!isset($_SESSION['idLista'])) {
        // Si aún no hay idLista (ni existente ni recién creado por spAgregarOfertaAceptadaAlCarrito, por ejemplo)
        echo json_encode([]);
        exit();
    }
}

$idLista = $_SESSION['idLista'];

if (!$idLista) {
    echo json_encode([]);
    exit();
}

// Consulta de los productos del carrito
// Usamos COALESCE para tomar precioUnitarioCompra si existe, sino el precio del producto.
// También traemos el tipo de producto para que el frontend pueda decidir si muestra los botones de +/- cantidad.
$query = "
    SELECT 
        p.idProducto,
        p.nombre,
        p.tipo AS tipoProducto, -- Para saber si es 'Venta' o 'Cotizacion'
        COALESCE(lp.precioUnitarioCompra, p.precio) AS precioFinal, -- Precio a mostrar y usar para cálculos
        lp.cantidad,
        lp.idMensajeOferta, -- Para saber si vino de una oferta
        (
            SELECT mp.url
            FROM Multimedia_Producto mp
            WHERE mp.idProducto = p.idProducto
            AND mp.url NOT LIKE '%.mp4'
            ORDER BY mp.idMultimedia ASC
            LIMIT 1
        ) AS imagenPrincipal
    FROM Lista_Producto lp
    INNER JOIN Producto p ON lp.idProducto = p.idProducto
    WHERE lp.idLista = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Error al preparar la consulta getCarrito: " . $conn->error);
    echo json_encode(['error' => 'Error interno del servidor']);
    exit();
}

$stmt->bind_param("i", $idLista);
$stmt->execute();
$resultado = $stmt->get_result();

$productos = [];

if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        // Asegurarse que el precioFinal sea un número para el frontend
        $row['precioFinal'] = !is_null($row['precioFinal']) ? (float)$row['precioFinal'] : 0.00;
        $productos[] = $row;
    }
} else {
    error_log("Error al ejecutar la consulta getCarrito: " . $stmt->error);
}

$stmt->close();
while ($conn->more_results() && $conn->next_result()) { // Limpiar cualquier resultado múltiple
    if ($res = $conn->store_result()) {
        $res->free();
    }
}

echo json_encode($productos);

$conn->close();
?>
