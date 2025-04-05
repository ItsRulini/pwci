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

    if ($userData != null) {

        session_start();
        $_SESSION['usuario'] = $userData; // Guardar en sesión

        // Switch para redirigir según el rol del usuario
        switch ($userData->getRol()) {
            case 'SuperAdmin':
                // Redirigir a la página de administración
                header("Location: ../views/superAdministrador/main.html");
                break;
            case 'Admin':
                // Redirigir a la página de administración
                header("Location: ../views/administrador/main.html");
                break;
            case 'Vendedor':
                // Redirigir a la página del vendedor
                header("Location: ../views/vendedor/main.html");
                break;
            case 'Comprador':
                // Redirigir a la página del cliente
                header("Location: ../views/cliente/main.html");
                break;
            default:
                // Redirigir a una página de error o acceso denegado
                header("Location: ../views/error.html");
                break;
        }
        exit();

    } else {
        // Si las credenciales son incorrectas, redirige al login con un mensaje
        header("Location: ../views/index.php?error=1");
        exit();
    }

}
?>