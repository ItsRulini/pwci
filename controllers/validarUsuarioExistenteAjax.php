    <?php
    require_once '../connection/conexion.php';
    require_once '../models/Usuario.php';
    require_once '../repositories/UsuarioDAO.php';

    session_start();
    header('Content-Type: application/json');

    $nombreUsuario = isset($_GET['usuario']) ? trim($_GET['usuario']) : '';
    $idUsuarioActual = 0;

    if (isset($_SESSION['usuario']) && $_SESSION['usuario'] instanceof Usuario) {
        $idUsuarioActual = $_SESSION['usuario']->getIdUsuario();
    }

    if (empty($nombreUsuario)) {
        echo json_encode(['valid' => false, 'message' => 'El nombre de usuario no puede estar vacío.']);
        exit();
    }

    $usuarioDAO = new UsuarioDAO($conn);
    // Similar al email, necesitamos un método que pueda ignorar el nombre de usuario actual.
    // Usaremos el SP directamente por ahora.

    $stmt = $conn->prepare("CALL spValidarUsuarioExistente(?)");
    $stmt->bind_param("s", $nombreUsuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $response = null;
    if ($result) {
        $response = $result->fetch_assoc(); // Debería ser ['Mensaje' => 'Usuario ya en uso'] o nada
        $result->free();
    }
    $stmt->close();
    while ($conn->more_results() && $conn->next_result()) { if ($res = $conn->store_result()) { $res->free(); }}

    // Si el nombre de usuario es el del usuario actual, se considera válido para él mismo.
    if ($idUsuarioActual > 0) {
        $stmtCurrent = $conn->prepare("SELECT nombreUsuario FROM Usuario WHERE idUsuario = ?");
        $stmtCurrent->bind_param("i", $idUsuarioActual);
        $stmtCurrent->execute();
        $resultCurrent = $stmtCurrent->get_result();
        $currentUserData = $resultCurrent->fetch_assoc();
        $stmtCurrent->close();
        while ($conn->more_results() && $conn->next_result()) { if ($res = $conn->store_result()) { $res->free(); }}

        if ($currentUserData && $currentUserData['nombreUsuario'] === $nombreUsuario) {
            echo json_encode(['valid' => true]);
            $conn->close();
            exit();
        }
    }

    if ($response && isset($response['Mensaje'])) {
        echo json_encode(['valid' => false, 'message' => $response['Mensaje']]);
    } else {
        echo json_encode(['valid' => true]);
    }

    $conn->close();
    ?>
    