    <?php
    require_once '../connection/conexion.php';
    require_once '../repositories/ProductoDAO.php';

    header('Content-Type: application/json');

    $idProducto = isset($_GET['idProducto']) ? (int)$_GET['idProducto'] : 0;

    if ($idProducto <= 0) {
        echo json_encode(['success' => false, 'comentarios' => [], 'message' => 'ID de producto no válido.']);
        exit();
    }

    $productoDAO = new ProductoDAO($conn);
    $comentarios = $productoDAO->obtenerComentariosDeProducto($idProducto);

    // No es estrictamente necesario el 'success' aquí si siempre devuelves un array
    echo json_encode(['success' => true, 'comentarios' => $comentarios]);

    $conn->close();
    ?>
    