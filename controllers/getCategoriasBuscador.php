<?php
require_once '../connection/conexion.php';
require_once '../repositories/CategoriaDAO.php';

header('Content-Type: application/json');

$dao = new CategoriaDAO($conn);
echo json_encode($dao->getCategorias());
?>