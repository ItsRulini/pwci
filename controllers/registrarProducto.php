<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../connection/conexion.php';
require_once '../models/Producto.php';
require_once '../models/Usuario.php'; // <-- ESTE FALTABA
require_once '../repositories/ProductoDAO.php';
session_start();

header('Content-Type: application/json');

// Función para crear carpeta multimedia del producto
function crearCarpetaMultimedia($idProducto) {
    $ruta = "../multimedia/productos/" . $idProducto;
    if (!is_dir($ruta)) {
        mkdir($ruta, 0777, true);
    }
    return $ruta;
}

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto = new Producto();

    $producto->setNombre($_POST['producto'] ?? '');
    $producto->setDescripcion($_POST['descripcion'] ?? '');
    $producto->setTipo($_POST['tipo'] ?? 'Venta');
    $producto->setPrecio(isset($_POST['precio']) ? (float)$_POST['precio'] : null);
    $producto->setStock(isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0);

    $idVendedor = 0;
    if (isset($_SESSION['usuario'])) {
        $usuarioSesion = $_SESSION['usuario'];
        if (is_object($usuarioSesion) && method_exists($usuarioSesion, 'getIdUsuario')) {
            $idVendedor = $usuarioSesion->getIdUsuario();
        }
    }
    $producto->setIdVendedor($idVendedor);

    // Eliminar categorías duplicadas (por si acaso)
    if (isset($_POST['categoria'])) {
        $categorias = array_unique((array)$_POST['categoria']);
        $producto->setIdCategorias($categorias);
    } else {
        $producto->setIdCategorias([]);
    }

    $nombresArchivos = [];

    if (isset($_FILES['imagenes'])) {
        foreach ($_FILES['imagenes']['name'] as $nombreArchivo) {
            $nombresArchivos[] = $nombreArchivo;
        }
    }

    if (isset($_FILES['video'])) {
        $nombresArchivos[] = $_FILES['video']['name'];
    }

    $producto->setArchivosMultimediaNombres($nombresArchivos);

    if ($conn->connect_error) {
        echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
        exit();
    }

    $productoDAO = new ProductoDAO($conn);

    $resultado = $productoDAO->insertarProducto($producto);

    if ($resultado['success']) {
        // Como no podemos confiar en $conexion->insert_id, sacamos el último ID así:
        $queryId = $conn->query("SELECT MAX(idProducto) AS idProducto FROM Producto");
        if ($row = $queryId->fetch_assoc()) {
            $idProducto = $row['idProducto'];

            // Crear la carpeta del producto
            $rutaCarpeta = crearCarpetaMultimedia($idProducto);

            // Guardar archivos multimedia en la carpeta
            if (isset($_FILES['imagenes'])) {
                foreach ($_FILES['imagenes']['tmp_name'] as $key => $archivoTemporal) {
                    $nombreArchivo = basename($_FILES['imagenes']['name'][$key]);
                    move_uploaded_file($archivoTemporal, $rutaCarpeta . '/' . $nombreArchivo);
                }
            }

            if (isset($_FILES['video'])) {
                move_uploaded_file($_FILES['video']['tmp_name'], $rutaCarpeta . '/' . basename($_FILES['video']['name']));
            }

            $response = ["success" => true, "message" => "Producto registrado correctamente.", "idProducto" => $idProducto];
        } else {
            $response = ["success" => false, "message" => "No se pudo obtener el ID del producto."];
        }
    } else {
        $response = ["success" => false, "message" => $resultado['message']];
    }
} else {
    $response = ["success" => false, "message" => "Método no permitido."];
}

echo json_encode($response);
?>
