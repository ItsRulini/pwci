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
    // $avatar se definirá más adelante después de procesar la imagen
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

    // --- Inicio: Manejo Mejorado de la Imagen ---
    $avatarParaBD = null; // Nombre del archivo que se guardará en la BD
    $rutaCompletaParaMover = null; // Ruta completa donde se moverá el archivo
    $subidaDeArchivoExitosa = true; // Asumimos éxito hasta que algo falle

    // Verificar si se subió un archivo y no hubo errores
    if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == UPLOAD_ERR_OK) {
        $carpeta = "../multimedia/imagenPerfil/";
        
        // Obtener la extensión original del archivo
        $nombreOriginal = $_FILES["avatar"]["name"];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION)); // ej: "jpg", "png"

        // Validar extensiones permitidas (opcional pero recomendado)
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $extensionesPermitidas)) {
            exit(json_encode(["success" => false, "message" => "Error: Tipo de archivo no permitido. Solo se aceptan JPG, JPEG, PNG, GIF."]));
        }

        // Crear el nuevo nombre de archivo: nombreUsuario.extensionOriginal
        $nombreArchivo = $usuario . "." . $extension;
        $rutaCompletaParaMover = $carpeta . $nombreArchivo;
        $avatarParaBD = $nombreArchivo; // Este es el nombre que irá a la BD

    } else if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] != UPLOAD_ERR_NO_FILE) {
        // Hubo un error diferente a "no se subió archivo"
        exit(json_encode(["success" => false, "message" => "Error al subir la imagen. Código: " . $_FILES["avatar"]["error"]]));
    }
    // Si no se subió archivo (UPLOAD_ERR_NO_FILE), $avatarParaBD seguirá siendo null (o puedes poner un default.jpg)

    // --- Fin: Manejo Mejorado de la Imagen ---

    // Crear un nuevo usuario
    $nuevoUsuario = new Usuario();
    $nuevoUsuario->setNombreUsuario($usuario);
    $nuevoUsuario->setEmail($email);
    $nuevoUsuario->setContraseña($pass); // Considera hashear la contraseña aquí antes de pasarla al DAO
    $nuevoUsuario->setFotoAvatar($avatarParaBD); // Usar el nombre de archivo definido
    $nuevoUsuario->setNombres($nombres);
    $nuevoUsuario->setPaterno($paterno);
    $nuevoUsuario->setMaterno($materno);
    $nuevoUsuario->setFechaNacimiento($nacimiento);
    $nuevoUsuario->setRol($rol);
    $nuevoUsuario->setGenero($sexo);

    // Llamando al dao para crear un nuevo usuario
    $good = $usuarioDAO->registrarUsuario($nuevoUsuario);

    if ($good) {
        // Mover el archivo solo si se subió uno y la ruta está definida
        if ($avatarParaBD !== null && $rutaCompletaParaMover !== null) {
            if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $rutaCompletaParaMover)) {
                // El registro en BD fue exitoso, pero la imagen no se pudo mover.
                // Podrías considerar eliminar el registro de usuario o notificar al usuario
                // que su perfil se creó pero sin imagen. Por ahora, enviamos error.
                exit(json_encode(["success" => false, "message" => "Usuario registrado, pero ocurrió un error al subir la imagen."]));
            }
        }
        exit(json_encode(["success" => true, "message" => "Usuario registrado correctamente."]));
    } else {
        exit(json_encode(["success" => false, "message" => "Error al registrar usuario."]));
    }
}
?>