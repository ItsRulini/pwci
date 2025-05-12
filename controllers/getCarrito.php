<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';

session_start();
    header('Content-Type: application/json');

    // Validar sesión
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['idLista'])) {
        echo json_encode([]);
        exit();
    }

    // Obtener el idLista de la sesión
    $idLista = $_SESSION['idLista'];

    // Si por alguna razón no hay carrito activo
    if (!$idLista) {
        echo json_encode([]);
        exit();
    }

    // Consulta de los productos del carrito
    $query = "
        SELECT 
            p.idProducto,
            p.nombre,
            p.precio,
            lp.cantidad,
            (
                SELECT mp.url
                FROM Multimedia_Producto mp
                WHERE mp.idProducto = p.idProducto
                AND mp.url NOT LIKE '%.mp4'
                ORDER BY mp.idMultimedia ASC
                LIMIT 1
            ) AS imagenPrincipal
        FROM Lista_Producto lp
        INNER JOIN Producto p ON lp.idProducto = p.idProducto
        WHERE lp.idLista = ?
    ";

    // Usar prepared statements (más seguro)
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idLista);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $productos = [];

    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $productos[] = $row;
        }
    }

    echo json_encode($productos);
?>
