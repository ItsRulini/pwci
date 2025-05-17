    <?php
    require_once '../connection/conexion.php';
    require_once '../repositories/ProductoDAO.php';

    header('Content-Type: application/json');

    $idProducto = isset($_GET['idProducto']) ? (int)$_GET['idProducto'] : 0;

    if ($idProducto <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de producto no válido.']);
        exit();
    }

    $productoDAO = new ProductoDAO($conn);
    $detalles = $productoDAO->obtenerDetallesProductoCliente($idProducto);

    if ($detalles) {
        // El SP spObtenerDetallesProductoCliente ya devuelve multimedia y categorías como strings
        // Si prefieres arrays, puedes procesar $detalles['urlsMultimedia'] y $detalles['nombreCategorias'] aquí
        // o ajustar el SP para que no use GROUP_CONCAT y devuelva múltiples filas (más complejo de manejar en PHP para un solo fetch).
        // Por ahora, el JS se encargará de separar los strings.
        echo json_encode(['success' => true, 'producto' => $detalles]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado o no disponible.']);
    }

    $conn->close();
    ?>
    