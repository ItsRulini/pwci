<?php
require_once '../connection/conexion.php';

header('Content-Type: application/json');

$sql = "SELECT idCategoria, nombre, descripcion FROM Categoria";
$result = $conn->query($sql);

$categorias = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
}

echo json_encode($categorias); // 👈 DEVOLVER ARRAY DIRECTO
?>
