    <?php
    require_once '../connection/conexion.php';
    require_once '../models/Usuario.php';
    require_once '../repositories/CarritoDAO.php'; // Contiene crearWishlist

    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado o método incorrecto.']);
        exit();
    }

    $usuarioActual = $_SESSION['usuario'];
    $idUsuario = $usuarioActual->getIdUsuario();

    $nombreLista = isset($_POST['nombreLista']) ? trim($_POST['nombreLista']) : '';
    $descripcionLista = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $privacidadLista = isset($_POST['listaPrivacidad']) ? $_POST['listaPrivacidad'] : 'Privada'; // Valor por defecto

    if (empty($nombreLista)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de la wishlist es obligatorio.']);
        exit();
    }
    if (!in_array($privacidadLista, ['Privada', 'Publica'])) {
        echo json_encode(['success' => false, 'message' => 'Valor de privacidad no válido.']);
        exit();
    }

    $carritoDAO = new CarritoDAO($conn);
    $resultado = $carritoDAO->crearWishlist($idUsuario, $nombreLista, $descripcionLista, $privacidadLista);

    // El DAO ahora devuelve un array con 'status', 'message', y 'idLista'
    if (isset($resultado['status']) && $resultado['status'] === 'SUCCESS') {
        echo json_encode(['success' => true, 'message' => $resultado['message'], 'idLista' => $resultado['idLista']]);
    } else {
        echo json_encode(['success' => false, 'message' => $resultado['message'] ?? 'Error al crear la wishlist.']);
    }

    $conn->close();
    ?>
    