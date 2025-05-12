<?php
require_once '../connection/conexion.php';

class CarritoDAO {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function agregarProductoAlCarrito($idUsuario, $idProducto) {
        try {
            $stmt = $this->conn->prepare("CALL spAgregarProductoAlCarrito(?, ?)");
            $stmt->bind_param("ii", $idUsuario, $idProducto);

            if ($stmt->execute()) {
                return ["success" => true, "message" => "Producto agregado al carrito."];
            } else {
                return ["success" => false, "message" => "Error al agregar producto: " . $stmt->error];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Excepción: " . $e->getMessage()];
        }
    }

    public function sumarCantidad($idLista, $idProducto) {
        $stmt = $this->conn->prepare("CALL spSumarCantidadProducto(?, ?)");
        $stmt->bind_param("ii", $idLista, $idProducto);
        return $stmt->execute();
    }

    public function restarCantidad($idLista, $idProducto) {
        $stmt = $this->conn->prepare("CALL spRestarCantidadProducto(?, ?)");
        $stmt->bind_param("ii", $idLista, $idProducto);
        return $stmt->execute();
    }

    public function eliminarProducto($idLista, $idProducto) {
        $stmt = $this->conn->prepare("CALL spEliminarProductoCarrito(?, ?)");
        $stmt->bind_param("ii", $idLista, $idProducto);
        return $stmt->execute();
    }

    public function vaciarCarrito($idLista) {
        $stmt = $this->conn->prepare("CALL spVaciarCarrito(?)");
        $stmt->bind_param("i", $idLista);
        return $stmt->execute();
    }

    public function obtenerCarritoPorUsuario($idUsuario) {
        $stmt = $this->conn->prepare("CALL spGetCarritoUsuario(?)");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Retorna un array tipo ["idLista" => ...]
    }

    public function obtenerWishlistsPorUsuario($idUsuario) {
        $stmt = $this->conn->prepare("CALL spGetWishlistsUsuario(?)");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $wishlists = [];
        while ($row = $result->fetch_assoc()) {
            $wishlists[] = $row;
        }
        return $wishlists;
    }

}
?>
