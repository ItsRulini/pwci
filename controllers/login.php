<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/UsuarioDAO.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"];
    $pass = $_POST["pass"];

    if ($conn->connect_error) {
        die(json_encode(["success" => false, "message" => "Error de conexión"]));
    }

    // Llamando al dao
    $usuarioDAO = new UsuarioDAO($conn);
    $userData = $usuarioDAO->loginUsuario($usuario, $pass);

    if( $userData != null) {

        session_start();
        $_SESSION['usuario'] = $userData; // Guardar en sesión

        // Redirigir a la página principal
        header("Location: ../main.html");
        exit();

    }
    else {
        // Si las credenciales son incorrectas, redirige al login con un mensaje
        header("Location: ../index.php?error=1");
        exit();
    }
    
}
?>
