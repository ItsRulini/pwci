<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';
require_once '../repositories/UsuarioDAO.php';

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

    // Manejo de la escritura del archivo en la carpeta
    $carpeta = "../imagenPerfil/";
    $nombreArchivo = $usuario . basename($_FILES["avatar"]["name"]);
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
    $usuarioDAO = new UsuarioDAO($conn);
    $good = $usuarioDAO->registrarUsuario($nuevoUsuario);

    if($good){

        if (!move_uploaded_file($_FILES["avatar"]["tmp_name"],$ruta)) {
            echo $ruta;
            die(json_encode(["success" => false, "message" => "Error al subir la imagen."]));
        }

        header("Location: ../views/index.php");
        exit();
    }
}

?>