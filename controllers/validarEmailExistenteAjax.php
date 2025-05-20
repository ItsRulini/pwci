    <?php
    require_once '../connection/conexion.php';
    require_once '../models/Usuario.php'; // Para obtener el idUsuario actual si es necesario
    require_once '../repositories/UsuarioDAO.php'; // Asumiendo que aquí está el método o lo añadirás

    session_start();
    header('Content-Type: application/json');

    $email = isset($_GET['email']) ? trim($_GET['email']) : '';
    $idUsuarioActual = 0;

    if (isset($_SESSION['usuario']) && $_SESSION['usuario'] instanceof Usuario) {
        $idUsuarioActual = $_SESSION['usuario']->getIdUsuario();
    }

    if (empty($email)) {
        echo json_encode(['valid' => false, 'message' => 'El correo no puede estar vacío.']);
        exit();
    }

    $usuarioDAO = new UsuarioDAO($conn);
    // Necesitas un método en UsuarioDAO que llame a spValidarEmailExistente
    // pero que permita ignorar el email del usuario actual si se está editando.
    // Por ahora, usaremos el SP directamente para simplificar,
    // asumiendo que spValidarEmailExistente solo devuelve 'Correo ya en uso' si existe.

    $stmt = $conn->prepare("CALL spValidarEmailExistente(?)");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $response = null;
    if ($result) {
        $response = $result->fetch_assoc(); // Debería ser ['Mensaje' => 'Correo ya en uso'] o nada
        $result->free();
    }
    $stmt->close();
    while ($conn->more_results() && $conn->next_result()) { if ($res = $conn->store_result()) { $res->free(); }}

    // Si el correo es el del usuario actual, se considera válido para él mismo.
    if ($idUsuarioActual > 0) {
        $stmtCurrent = $conn->prepare("SELECT email FROM Usuario WHERE idUsuario = ?");
        $stmtCurrent->bind_param("i", $idUsuarioActual);
        $stmtCurrent->execute();
        $resultCurrent = $stmtCurrent->get_result();
        $currentUserData = $resultCurrent->fetch_assoc();
        $stmtCurrent->close();
        while ($conn->more_results() && $conn->next_result()) { if ($res = $conn->store_result()) { $res->free(); }}

        if ($currentUserData && $currentUserData['email'] === $email) {
            echo json_encode(['valid' => true]); // Es el email del usuario actual, válido para él
            $conn->close();
            exit();
        }
    }


    if ($response && isset($response['Mensaje'])) { // El SP devuelve 'Mensaje' si está en uso
        echo json_encode(['valid' => false, 'message' => $response['Mensaje']]);
    } else {
        echo json_encode(['valid' => true]); // No se encontró el mensaje, así que el correo está disponible
    }

    $conn->close();
    ?>
