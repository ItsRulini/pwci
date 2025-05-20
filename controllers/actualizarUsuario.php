<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php'; 
require_once '../repositories/UsuarioDAO.php'; 

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/index.php?error=" . urlencode("Acceso denegado. Inicia sesión."));
    exit();
}

$usuarioActual = $_SESSION['usuario'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idUsuario = $usuarioActual->getIdUsuario(); 

    $nombreUsuario = trim($_POST["usuario"]);
    $email = trim($_POST["email"]);
    $nombres = trim($_POST["nombres"]);
    $paterno = trim($_POST["paterno"]);
    $materno = trim($_POST["materno"]);
    $fechaNacimiento = $_POST["nacimiento"];
    $privacidad = isset($_POST["privacidad"]) ? $_POST["privacidad"] : $usuarioActual->getPrivacidad(); // Mantener actual si no se envía
    
    $nuevaContraseña = $_POST["password"]; // No hacer trim a la contraseña

    $fotoAvatarNombre = $usuarioActual->getFotoAvatar(); 

    $rolDirectorio = strtolower($usuarioActual->getRol());
    switch($rolDirectorio) {
        case "superadmin": $rolDirectorio = "superAdministrador"; break;
        case "admin": $rolDirectorio = "administrador"; break;
        case "vendedor": $rolDirectorio = "vendedor"; break;
        case "comprador": $rolDirectorio = "cliente"; break;
        // vendedor y cliente/comprador ya coinciden
    }

    // Validaciones del lado del servidor (importante tenerlas también, no solo en JS)
    if (empty($nombreUsuario) || strlen($nombreUsuario) < 3) {
        header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("Nombre de usuario debe tener al menos 3 caracteres."));
        exit();
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
         header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("Formato de correo no válido."));
        exit();
    }
    // ... (más validaciones del lado del servidor para nombres, apellidos, fecha)

    // Validar contraseña si se proporcionó una nueva
    if (!empty($nuevaContraseña)) {
        if (strlen($nuevaContraseña) < 8 ||
            !preg_match('/[A-Z]/', $nuevaContraseña) ||
            !preg_match('/[a-z]/', $nuevaContraseña) ||
            !preg_match('/[0-9]/', $nuevaContraseña) ||
            !preg_match('/[^A-Za-z0-9]/', $nuevaContraseña)) { // Carácter especial genérico
            header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("La contraseña no cumple los requisitos de complejidad."));
            exit();
        }
    }


    // Validar si el nuevo email o nombre de usuario ya existen (PARA OTRO USUARIO)
    $usuarioDAO = new UsuarioDAO($conn);
    if ($email !== $usuarioActual->getEmail() && $usuarioDAO->validarEmailExistente($email, $idUsuario)) { // Pasar idUsuario para excluir el actual
        header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("El correo electrónico ya está en uso por otro usuario."));
        exit();
    }
    if ($nombreUsuario !== $usuarioActual->getNombreUsuario() && $usuarioDAO->validarNombreUsuarioExistente($nombreUsuario, $idUsuario)) { // Pasar idUsuario
        header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("El nombre de usuario ya está en uso por otro usuario."));
        exit();
    }


    if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == 0) {
        $carpeta = "../multimedia/imagenPerfil/"; // Ruta corregida
        if (!file_exists($carpeta)) {
            mkdir($carpeta, 0777, true);
        }
        $extension = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $permitidas)) {
            header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("Formato de imagen no permitido."));
            exit();
        }
        // Generar nombre único o basado en idUsuario para evitar sobreescrituras y problemas de caché
        $fotoAvatarNombre = "avatar_" . $idUsuario . "_" . time() . "." . $extension;
        $rutaCompleta = $carpeta . $fotoAvatarNombre;

        if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $rutaCompleta)) {
            header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("Error al subir la nueva imagen."));
            exit();
        }
    }

    // Llamar al procedimiento almacenado spUpdateUsuario
    // Este SP necesita ser modificado para aceptar la contraseña y privacidad
    // y solo actualizar la contraseña si se proporciona una nueva.
    $actualizado = $usuarioDAO->actualizarUsuario(
        $idUsuario,
        $nombreUsuario,
        $email,
        $nombres,
        $paterno,
        $materno,
        $fotoAvatarNombre,
        $fechaNacimiento,
        $privacidad, // Nuevo parámetro
        empty($nuevaContraseña) ? null : $nuevaContraseña // Nuevo parámetro, null si no se cambia
    );


    if ($actualizado) {
        $_SESSION['usuario']->setNombreUsuario($nombreUsuario);
        $_SESSION['usuario']->setEmail($email);
        $_SESSION['usuario']->setNombres($nombres);
        $_SESSION['usuario']->setPaterno($paterno);
        $_SESSION['usuario']->setMaterno($materno);
        $_SESSION['usuario']->setFotoAvatar($fotoAvatarNombre);
        $_SESSION['usuario']->setFechaNacimiento($fechaNacimiento);
        $_SESSION['usuario']->setPrivacidad($privacidad);
        if (!empty($nuevaContraseña)) {
            $_SESSION['usuario']->setContraseña($nuevaContraseña); // Actualizar en sesión si cambió
        }
        
        header("Location: ../views/" . $rolDirectorio . "/perfil.php?success=" . urlencode("Perfil actualizado correctamente."));
    } else {
        header("Location: ../views/" . $rolDirectorio . "/perfil.php?error=" . urlencode("Error al actualizar el perfil o no se realizaron cambios."));
    }

    $conn->close();
    exit();

} else {
    header("Location: ../views/index.php");
    exit();
}
?>
