<?php
require_once '../connection/conexion.php'; // Ya incluido donde se instancia
require_once '../models/Producto.php';   // Ya incluido
require_once '../models/Usuario.php';    // Para obtener el idVendedor

class ProductoDAO {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function insertarProducto(Producto $producto) {
        try {
            $stmt = $this->conn->prepare("CALL InsertarProducto(?, ?, ?, ?, ?, ?, ?, ?)");

            // Variables separadas para bind_param
            $nombre = $producto->getNombre();
            $descripcion = $producto->getDescripcion();
            $tipo = $producto->getTipo();
            $precio = $producto->getPrecio();
            $stock = $producto->getStock();
            $idVendedor = $producto->getIdVendedor();
            $categoriasString = implode(",", $producto->getIdCategorias());
            $multimediaString = implode(",", $producto->getArchivosMultimediaNombres());

            $stmt->bind_param(
                "sssdiiss",
                $nombre,
                $descripcion,
                $tipo,
                $precio,
                $stock,
                $idVendedor,
                $categoriasString,
                $multimediaString
            );

            if ($stmt->execute()) {
                // Retornar success y también el id del producto (si puedes extraerlo mejor)
                return ["success" => true];
            } else {
                return ["success" => false, "message" => "Error al insertar producto: " . $stmt->error];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Excepción: " . $e->getMessage()];
        }
    }
}

?>