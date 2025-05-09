<?php
require_once '../connection/conexion.php';
require_once '../models/Categoria.php';
require_once '../models/Usuario.php'; // Para obtener el tipo de $_SESSION['usuario']
require_once '../repositories/CategoriaDAO.php';

session_start();

header('Content-Type: application/json'); // Importante para la respuesta AJAX

if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    echo json_encode(["success" => false, "message" => "Acceso denegado. Debes iniciar sesión."]);
    exit();
}

// Solo los vendedores pueden crear categorías (o ajusta según tu lógica de roles)
if ($_SESSION['usuario']->getRol() !== 'Vendedor') {
    echo json_encode(["success" => false, "message" => "No tienes permiso para crear categorías."]);
    exit();
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = isset($_POST["nombre"]) ? trim($_POST["nombre"]) : '';
    $descripcion = isset($_POST["descripcion"]) ? trim($_POST["descripcion"]) : '';
    $idCreador = $_SESSION['usuario']->getIdUsuario(); // El vendedor logueado es el creador

    if (empty($nombre) || empty($descripcion)) {
        echo json_encode(["success" => false, "message" => "El nombre y la descripción son obligatorios."]);
        exit();
    }

    if ($conn->connect_error) {
        echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
        exit();
    }

    $categoria = new Categoria();
    $categoria->setNombre($nombre);
    $categoria->setDescripcion($descripcion);
    $categoria->setIdCreador($idCreador);

    $categoriaDAO = new CategoriaDAO($conn);
    $registrado = $categoriaDAO->insertarCategoria($categoria);

    if ($registrado) {
        // Podrías devolver la categoría creada si el frontend la necesita inmediatamente
        // Por ahora, solo confirmación.
        echo json_encode(["success" => true, "message" => "Categoría registrada correctamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al registrar la categoría."]);
    }

    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>