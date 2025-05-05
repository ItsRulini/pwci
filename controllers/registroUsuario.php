<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/UsuarioDAO.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"];
    $usuario = $_POST["usuario"];
    $pass = $_POST["pass"];
    //$avatar = $_POST["avatar"];
    $nombres = $_POST["nombres"];
    $paterno = $_POST["paterno"];
    $materno = $_POST["materno"];
    $nacimiento = $_POST["nacimiento"];
    $rol = $_POST["rol"];
    $sexo = $_POST["sexo"];

    if ($conn->connect_error) {
        die(json_encode(["success" => false, "message" => "Error de conexión"]));
    }

    $usuarioDAO = new UsuarioDAO($conn);
    $userEmail = $usuarioDAO->validarCorreo($email);

    if (!$userEmail) {
        exit(json_encode(["success" => false, "message" => "El correo ya está en uso."]));
    }
    $userUser = $usuarioDAO->validarUsuario($usuario);

    if (!$userUser) {
        exit(json_encode(["success" => false, "message" => "El usuario ya está en uso."]));
    }

    // Manejo de la escritura del archivo en la carpeta
    $carpeta = "../multimedia/imagenPerfil/";
    $nombreArchivo = $usuario . ".jpg";// . basename($_FILES["avatar"]["name"]);
    $ruta = $carpeta . $nombreArchivo;

    $avatar = $nombreArchivo;

    // Crear un nuevo usuario
    $nuevoUsuario = new Usuario();
    $nuevoUsuario->setNombreUsuario($usuario);
    $nuevoUsuario->setEmail($email);
    $nuevoUsuario->setContraseña($pass);
    $nuevoUsuario->setFotoAvatar($avatar);
    $nuevoUsuario->setNombres($nombres);
    $nuevoUsuario->setPaterno($paterno);
    $nuevoUsuario->setMaterno($materno);
    $nuevoUsuario->setFechaNacimiento($nacimiento);
    $nuevoUsuario->setRol($rol);
    $nuevoUsuario->setGenero($sexo);

    // Llamando al dao para crear un nuevo usuario

    $good = $usuarioDAO->registrarUsuario($nuevoUsuario);

    if ($good) {
        if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $ruta)) {
            exit(json_encode(["success" => false, "message" => "Error al subir la imagen."]));
        }
        exit(json_encode(["success" => true, "message" => "Usuario registrado correctamente."]));
    } else {
        exit(json_encode(["success" => false, "message" => "Error al registrar usuario."]));
    }
}

?>