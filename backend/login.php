<?php
require 'conexion.php';
require '../models/Usuario.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"];
    $pass = $_POST["pass"];

    if ($conn->connect_error) {
        die(json_encode(["success" => false, "message" => "Error de conexión"]));
    }

    // Llamada al procedimiento almacenado
    $sql = "CALL spLogin(?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuario, $pass);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if( $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();

        $userData = new Usuario();
        $userData->setIdUsuario($fila["idUsuario"]);
        $userData->setNombreUsuario($fila["nombreUsuario"]);
        $userData->setContraseña($fila["contraseña"]);
        $userData->setEmail($fila["email"]);
        $userData->setFotoAvatar($fila["fotoAvatar"]);
        $userData->setNombres($fila["nombres"]);
        $userData->setPaterno($fila["paterno"]);
        $userData->setMaterno($fila["materno"]);
        $userData->setFechaNacimiento($fila["fechaNacimiento"]);
        $userData->setRol($fila["rol"]);
        $userData->setGenero($fila["genero"]);
        $userData->setPrivacidad($fila["privacidad"]);

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
