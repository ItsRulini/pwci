<?php
require_once '../connection/conexion.php';
require_once '../repositories/UsuarioDAO.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"];

    if ($conn->connect_error) {
        die(json_encode(["success" => false, "message" => "Error de conexión"]));
    }

    // Llamando al dao
    $usuarioDAO = new UsuarioDAO($conn);
    $userData = $usuarioDAO->validarUsuario($usuario);

    if ($userData) {
        echo json_encode(["success" => true, "message" => "El usuario está disponible."]);
    } else {
        echo json_encode(["success" => false, "message" => "El usuario ya está en uso."]);
    }

    $conn->close(); // Cerrar la conexión a la base de datos
}


?>