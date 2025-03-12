<?php
require 'conexion.php';
require '../models/Usuario.php';

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

    if (!move_uploaded_file($_FILES["avatar"]["tmp_name"],$ruta)) {
        echo $ruta;
        die(json_encode(["success" => false, "message" => "Error al subir la imagen."]));
    }

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
    
    // Llamada al procedimiento almacenado
    $sql = "CALL spInsertUsuario(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die(json_encode(["success" => false, "message" => "Error en la preparación de la consulta: " . $conn->error]));
    }

    $rol = $nuevoUsuario->getRol();
    $usuario = $nuevoUsuario->getNombreUsuario();
    $pass = $nuevoUsuario->getContraseña();
    $email = $nuevoUsuario->getEmail();
    $nombres = $nuevoUsuario->getNombres();
    $paterno = $nuevoUsuario->getPaterno();
    $materno = $nuevoUsuario->getMaterno();
    $avatar = $nuevoUsuario->getFotoAvatar();
    $genero = $nuevoUsuario->getGenero();
    $nacimiento = $nuevoUsuario->getFechaNacimiento();

    // Asociar parámetros desde el objeto Usuario
    $stmt->bind_param(
        "ssssssssss",
        $rol,
        $usuario,
        $pass,
        $email,
        $nombres,
        $paterno,
        $materno,
        $avatar,
        $genero,
        $nacimiento
    );

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Usuario registrado con éxito"]);
        $good = true;
        
    } else {
        echo json_encode(["success" => false, "message" => "Error en la ejecución: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

    if($good){
        header("Location: ../index.php");
        exit();
    }
}

?>