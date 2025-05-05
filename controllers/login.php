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

    if ($userData instanceof Usuario) {

        session_start();
        $_SESSION['usuario'] = $userData; // Guardar en sesión

        // Switch para redirigir según el rol del usuario
        switch ($userData->getRol()) {
            case 'SuperAdmin':

                //$_SESSION['usuarios'] = $usuarioDAO->getUsuariosRegistrados(); // Guardar en sesión

                $conn->close(); // Cerrar la conexión a la base de datos
                // Redirigir a la página del superadministrador
                header("Location: ../views/superAdministrador/main.php");
                break;
            case 'Admin':
                // Redirigir a la página del administrador
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
        // $userData trae un mensaje de error
        header("Location: ../views/index.php?error=" . urlencode($userData));
        exit();
    }

}
?>