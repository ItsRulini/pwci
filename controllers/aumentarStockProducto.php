    <?php
    require_once '../connection/conexion.php';
    require_once '../models/Usuario.php';
    require_once '../repositories/ProductoDAO.php';

    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SESSION['usuario']->getRol() !== 'Vendedor') {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo vendedores pueden modificar stock.']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
        exit();
    }

    $vendedor = $_SESSION['usuario'];
    $idVendedor = $vendedor->getIdUsuario();

    $idProducto = isset($_POST['idProducto']) ? (int)$_POST['idProducto'] : 0;
    $cantidadAAgregar = isset($_POST['cantidadAAgregar']) ? (int)$_POST['cantidadAAgregar'] : 0;

    if ($idProducto <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de producto no válido.']);
        exit();
    }
    if ($cantidadAAgregar <= 0) {
        echo json_encode(['success' => false, 'message' => 'La cantidad a agregar debe ser un número positivo.']);
        exit();
    }

    $productoDAO = new ProductoDAO($conn);
    $resultado = $productoDAO->aumentarStockProducto($idProducto, $idVendedor, $cantidadAAgregar);

    // El DAO ahora devuelve un array con 'status', 'message', y 'nuevoStock'
    if (isset($resultado['status']) && $resultado['status'] === 'SUCCESS') {
        echo json_encode([
            'success' => true, 
            'message' => $resultado['message'], 
            'nuevoStock' => $resultado['nuevoStock']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => $resultado['message'] ?? 'Error al actualizar el stock.',
            'status_detail' => $resultado['status'] ?? 'FAIL_UNKNOWN' // Para depuración
        ]);
    }

    $conn->close();
    ?>
    