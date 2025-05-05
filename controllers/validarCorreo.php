<?php
require_once '../connection/conexion.php';
require_once '../repositories/UsuarioDAO.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];

    if ($conn->connect_error) {
        die(json_encode(["success" => false, "message" => "Error de conexión"]));
    }

    // Llamando al dao
    $usuarioDAO = new UsuarioDAO($conn);
    $userData = $usuarioDAO->validarCorreo($email);

    if ($userData) {
        echo json_encode(["success" => true, "message" => "El correo está disponible."]);
    } else {
        echo json_encode(["success" => false, "message" => "El correo ya está en uso."]);
    }

    $conn->close(); // Cerrar la conexión a la base de datos
}


?>