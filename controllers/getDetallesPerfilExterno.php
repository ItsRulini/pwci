    <?php
    require_once '../connection/conexion.php';
    require_once '../models/Usuario.php'; // Para instanceof
    require_once '../repositories/UsuarioDAO.php';
    require_once '../repositories/CarritoDAO.php'; // Para wishlists
    require_once '../repositories/ProductoDAO.php'; // Para productos de vendedor

    session_start();
    header('Content-Type: application/json');

    // El usuario que visita debe estar logueado
    if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debes iniciar sesión.']);
        exit();
    }

    $idUsuarioConsultado = isset($_GET['idUsuario']) ? (int)$_GET['idUsuario'] : 0;

    if ($idUsuarioConsultado <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario no válido.']);
        exit();
    }

    $usuarioDAO = new UsuarioDAO($conn);
    $perfilExterno = $usuarioDAO->getDetallesPerfilExterno($idUsuarioConsultado);

    if (!$perfilExterno) {
        echo json_encode(['success' => false, 'message' => 'Perfil no encontrado o no accesible.']);
        $conn->close();
        exit();
    }

    // Preparar datos adicionales según el rol y privacidad
    $datosAdicionales = [];
    if ($perfilExterno['rol'] === 'Comprador') {
        if ($perfilExterno['privacidad'] === 'Publico') {
            $carritoDAO = new CarritoDAO($conn);
            $wishlists = $carritoDAO->getWishlistsPublicasDeUsuario($idUsuarioConsultado);
            // Para cada wishlist, obtener sus productos
            foreach ($wishlists as &$wishlist) { // Pasar por referencia para modificar
                $wishlist['productos'] = $carritoDAO->getProductosDeLista($wishlist['idLista'], $idUsuarioConsultado);
            }
            unset($wishlist); // Romper la referencia
            $datosAdicionales['wishlists'] = $wishlists;
        } else {
            // Perfil de Comprador es Privado
            $datosAdicionales['mensajePrivacidad'] = 'Este perfil de comprador es privado.';
        }
    } elseif ($perfilExterno['rol'] === 'Vendedor') {
        // Vendedores son siempre públicos según tu lógica
        $productoDAO = new ProductoDAO($conn);
        $datosAdicionales['productos'] = $productoDAO->getProductosVisiblesVendedor($idUsuarioConsultado);
    }

    echo json_encode([
        'success' => true,
        'perfil' => $perfilExterno,
        'contenidoAdicional' => $datosAdicionales
    ]);

    $conn->close();
    ?>
    