    <?php
    require_once '../connection/conexion.php';
    require_once '../models/Usuario.php';
    require_once '../repositories/CarritoDAO.php'; // Contiene obtenerWishlistsPorUsuario

    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
        echo json_encode(['success' => false, 'wishlists' => [], 'message' => 'Usuario no autenticado.']);
        exit();
    }

    $usuarioActual = $_SESSION['usuario'];
    $idUsuario = $usuarioActual->getIdUsuario();

    $carritoDAO = new CarritoDAO($conn); // Usamos CarritoDAO ya que ahí está el método
    $wishlists = $carritoDAO->obtenerWishlistsPorUsuario($idUsuario);

    // Actualizar la sesión con las wishlists más recientes (opcional pero bueno)
    $_SESSION['wishlists'] = $wishlists;

    if ($wishlists !== null) { // obtenerWishlistsPorUsuario devuelve array, incluso vacío
        echo json_encode(['success' => true, 'wishlists' => $wishlists]);
    } else {
        // Este caso es menos probable si el DAO devuelve array vacío en lugar de null en error
        echo json_encode(['success' => false, 'wishlists' => [], 'message' => 'Error al obtener las wishlists.']);
    }

    $conn->close();
    ?>
    