<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/UsuarioDAO.php';

if (isset($_GET['action']) && $_GET['action'] === 'listar') {
    
    if ($conn->connect_error) {
        die(json_encode(["success" => false, "message" => "Error de conexión"]));
    }


    // Asegúrate de validar el rol aquí si quieres
    $usuarioDAO = new UsuarioDAO($conn);
    $usuarios = $usuarioDAO->getUsuariosRegistrados(); // Método que consulta TODOS los usuarios en la base de datos
    
    $usuariosArray = [];
    foreach ($usuarios as $usuario) {
        $usuariosArray[] = [
            'idUsuario' => $usuario->getIdUsuario(),
            'nombreUsuario' => $usuario->getNombreUsuario(),
            'email' => $usuario->getEmail(),
            'rol' => $usuario->getRol(),
            'fechaRegistro' => $usuario->getFechaRegistro(),
            'estatus' => $usuario->getEstatus()
        ];
    }

    echo json_encode($usuariosArray);
    $conn->close(); // Cerrar la conexión a la base de datos
    exit();
}
?>
