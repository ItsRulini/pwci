<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php'; // Asegúrate que la ruta es correcta
require_once '../repositories/UsuarioDAO.php'; // Asegúrate que la ruta es correcta

session_start();

// Verificar si el usuario está logueado y tiene permiso para actualizar
// (Este es un ejemplo básico, puedes expandirlo según tus necesidades)
if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/index.php?error=" . urlencode("Acceso denegado. Inicia sesión."));
    exit();
}

// Obtener el usuario actual de la sesión para verificar roles si es necesario
$usuarioActual = $_SESSION['usuario'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar que el ID del usuario a modificar esté presente y sea el del usuario logueado
    // o que el usuario logueado tenga permisos de administrador para modificar otros perfiles.
    // Por simplicidad, este ejemplo asume que el usuario solo puede modificar su propio perfil.
    $idUsuario = $_SESSION['usuario']->getIdUsuario(); // Obtener el ID del usuario de la sesión

    // Recoger los datos del formulario
    $nombreUsuario = $_POST["usuario"];
    $email = $_POST["email"];
    $nombres = $_POST["nombres"];
    $paterno = $_POST["paterno"];
    $materno = $_POST["materno"];
    $fechaNacimiento = $_POST["nacimiento"];
    $fotoAvatarNombre = $_SESSION['usuario']->getFotoAvatar(); // Mantener la foto actual por defecto


    // Redirigir de vuelta a la página de perfil con un mensaje de éxito
    // La redirección dependerá del rol del usuario
    $rolDirectorio = strtolower($usuarioActual->getRol()); // ej. 'administrador', 'cliente'

    switch($rolDirectorio) {
        case "superadmin": $rolDirectorio = "superAdministrador"; break;
        case "admin": $rolDirectorio = "administrador"; break;
        case "vendedor": $rolDirectorio = "vendedor"; break;
        case "comprador": $rolDirectorio = "cliente"; break;
    }

    // Manejo de la nueva foto de avatar si se subió una
    if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == 0) {
        $carpeta = "../multimedia/imagenPerfil/";
        // Es buena práctica generar un nombre único o usar el nombre de usuario para evitar colisiones
        // y asegurar que la extensión sea la correcta.
        $extension = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
        $fotoAvatarNombre = $nombreUsuario . "." . $extension; // Ejemplo: juanperez.jpg
        $rutaCompleta = $carpeta . $fotoAvatarNombre;

        // Mover el archivo subido
        if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $rutaCompleta)) {
            // Manejar error de subida de archivo
            // Puedes redirigir con un mensaje de error o loguearlo
            header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("Error al subir la nueva imagen."));
            exit();
        }
    }

    // Validar conexión
    if ($conn->connect_error) {
        // Podrías redirigir a una página de error o mostrar un mensaje
        die(json_encode(["success" => false, "message" => "Error de conexión"]));
    }

    $usuarioDAO = new UsuarioDAO($conn);

    // Llamar al procedimiento almacenado
    // Asegúrate que el procedimiento almacenado spUpdateUsuario exista y funcione como esperas.
    // El procedure que proporcionaste no incluye el WHERE para idUsuario, ¡ES CRUCIAL AÑADIRLO!
    // De lo contrario, actualizarás TODOS los usuarios de la tabla.
    $actualizado = $usuarioDAO->actualizarUsuario(
        $idUsuario,
        $nombreUsuario,
        $email,
        $nombres,
        $paterno,
        $materno,
        $fotoAvatarNombre, // Pasar el nombre del archivo de la foto
        $fechaNacimiento
    );

    if ($actualizado) {
        // Actualizar los datos del usuario en la sesión
        $_SESSION['usuario']->setNombreUsuario($nombreUsuario);
        $_SESSION['usuario']->setEmail($email);
        $_SESSION['usuario']->setNombres($nombres);
        $_SESSION['usuario']->setPaterno($paterno);
        $_SESSION['usuario']->setMaterno($materno);
        $_SESSION['usuario']->setFotoAvatar($fotoAvatarNombre);
        $_SESSION['usuario']->setFechaNacimiento($fechaNacimiento);

        

        header("Location: ../views/" . $rolDirectorio . "/perfil.php?success=" . urlencode("Perfil actualizado correctamente."));
    } else {
        // Manejar error en la actualización
        header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("Error al actualizar el perfil."));
    }

    $conn->close();
    exit();

} else {
    // Si no es POST, redirigir o mostrar error
    header("Location: ../views/index.php");
    exit();
}
?>