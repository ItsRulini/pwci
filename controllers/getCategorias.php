<?php
require_once '../connection/conexion.php';
require_once '../models/Categoria.php';
require_once '../repositories/CategoriaDAO.php';

header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión.", "data" => []]);
    exit();
}

$categoriaDAO = new CategoriaDAO($conn);
$listaCategoriasModel = $categoriaDAO->getCategorias();

$categoriasParaJson = [];
foreach ($listaCategoriasModel as $categoria) {
    $categoriasParaJson[] = [
        'idCategoria' => $categoria->getIdCategoria(),
        'nombre' => $categoria->getNombre(),
        'descripcion' => $categoria->getDescripcion()
        // Añade más campos si los necesitas en el frontend
    ];
}

if (!empty($categoriasParaJson)) {
    echo json_encode(["success" => true, "message" => "Categorías obtenidas.", "data" => $categoriasParaJson]);
} else {
    echo json_encode(["success" => false, "message" => "No se encontraron categorías.", "data" => []]);
}

$conn->close();
?>