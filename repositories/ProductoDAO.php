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

    public function obtenerProductosPendientes() {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosParaAutorizacion()");
            $stmt->execute();
            $resultado = $stmt->get_result();

            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }

            $stmt->close();
        } catch (Exception $e) {
            error_log("Excepción en getProductosPendientes: " . $e->getMessage());
        }

        return $productos;
    }

    public function aprobarProducto($idProducto, $idAdministrador) {
        try {
            $stmt = $this->conn->prepare("CALL spAprobarProducto(?, ?)");
            $stmt->bind_param("ii", $idProducto, $idAdministrador);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            return false;
        }
    }

    public function rechazarProducto($idProducto, $idAdministrador) {
        try {
            $stmt = $this->conn->prepare("CALL spRechazarProducto(?, ?)");
            $stmt->bind_param("ii", $idProducto, $idAdministrador);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerProductoPorId($idProducto) {
        try {
            $stmt = $this->conn->prepare("CALL spGetProductoPorId(?)");
            $stmt->bind_param("i", $idProducto);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $producto = $resultado->fetch_assoc();
            $stmt->close();
            return $producto;
        } catch (Exception $e) {
            return null;
        }
    }

    public function obtenerMultimediaPorProducto($idProducto) {
        $archivos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetMultimediaProductoPorId(?)");
            $stmt->bind_param("i", $idProducto);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $archivos[] = $fila['url'];
            }
            $stmt->close();
        } catch (Exception $e) {
            // Manejar error
        }
        return $archivos;
    }

    public function obtenerCategoriasPorProducto($idProducto) {
        $categorias = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetCategoriasProductoPorId(?)");
            $stmt->bind_param("i", $idProducto);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $categorias[] = $fila['nombre'];
            }
            $stmt->close();
        } catch (Exception $e) {
            // Manejar error
        }
        return $categorias;
    }

    public function obtenerProductosAprobadosPorAdmin($idAdministrador) {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosAprobadosPorAdmin(?)");
            $stmt->bind_param("i", $idAdministrador);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $stmt->close();
        } catch (Exception $e) {
            // Manejar error
        }
        return $productos;
    }

    public function obtenerProductosRechazadosPorAdmin($idAdministrador) {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosRechazadosPorAdmin(?)");
            $stmt->bind_param("i", $idAdministrador);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $stmt->close();
        } catch (Exception $e) {
            // Manejar error
        }
        return $productos;
    }

    public function obtenerProductosPendientesVendedor($idVendedor) {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosPendientesVendedor(?)");
            $stmt->bind_param("i", $idVendedor);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $stmt->close();
        } catch (Exception $e) {}
        return $productos;
    }

    public function obtenerProductosAprobadosVendedor($idVendedor) {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosAprobadosVendedor(?)");
            $stmt->bind_param("i", $idVendedor);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $stmt->close();
        } catch (Exception $e) {}
        return $productos;
    }

    public function obtenerProductosRechazadosVendedor($idVendedor) {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosRechazadosVendedor(?)");
            $stmt->bind_param("i", $idVendedor);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $stmt->close();
        } catch (Exception $e) {}
        return $productos;
    }

    public function getProductosPopulares() {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosPopulares()");
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $stmt->close();
        } catch (Exception $e) {}
        return $productos;
    }

    public function getProductosCotizacion() {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosCotizacion()");
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $stmt->close();
        } catch (Exception $e) {}
        return $productos;
    }

    public function getProductosRecientes() {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosRecientes()");
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $stmt->close();
        } catch (Exception $e) {}
        return $productos;
    }

    public function getProductosGenerales() {
        $productos = [];
        try {
            $stmt = $this->conn->prepare("CALL spGetProductosGenerales()");
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $stmt->close();
        } catch (Exception $e) {}
        return $productos;
    }


    public function buscarProductosCliente($textoBusqueda) {
        $stmt = $this->conn->prepare("CALL spBuscarProductosCliente(?)");
        $stmt->bind_param("s", $textoBusqueda);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $productos = [];
        while ($row = $resultado->fetch_assoc()) {
            $productos[] = $row;
        }

        $stmt->close();
        return $productos;
    }

    public function buscarProductosFiltrados($query, $categoria, $precioMin, $precioMax) {
        $stmt = $this->conn->prepare("CALL spBuscarProductosFiltrados(?, ?, ?, ?)");
        $stmt->bind_param("ssdd", $query, $categoria, $precioMin, $precioMax);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $productos = [];
        while ($row = $resultado->fetch_assoc()) {
            $productos[] = $row;
        }

        $stmt->close();
        return $productos;
    }

    public function obtenerDetallesProductoCliente($idProducto) {
        $detalles = null;
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        $stmt = $this->conn->prepare("CALL spObtenerDetallesProductoCliente(?)");
        if (!$stmt) {
            error_log("ProductoDAO::obtenerDetallesProductoCliente - Error en prepare: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $idProducto);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            if ($resultado) {
                $detalles = $resultado->fetch_assoc();
                $resultado->free();
            }
        } else {
            error_log("ProductoDAO::obtenerDetallesProductoCliente - Error en execute: " . $stmt->error);
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        return $detalles;
    }

    /**
     * Obtiene los comentarios para un producto específico.
     *
     * @param int $idProducto
     * @return array Lista de comentarios.
     */
    public function obtenerComentariosDeProducto($idProducto) {
        $comentarios = [];
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        $stmt = $this->conn->prepare("CALL spObtenerComentariosDeProducto(?)");
        if (!$stmt) {
            error_log("ProductoDAO::obtenerComentariosDeProducto - Error en prepare: " . $this->conn->error);
            return $comentarios;
        }
        $stmt->bind_param("i", $idProducto);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            if ($resultado) {
                while ($fila = $resultado->fetch_assoc()) {
                    $comentarios[] = $fila;
                }
                $resultado->free();
            }
        } else {
            error_log("ProductoDAO::obtenerComentariosDeProducto - Error en execute: " . $stmt->error);
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        return $comentarios;
    }

    public function getProductosVisiblesVendedor($idVendedor) {
            $productos = [];
            while ($this->conn->more_results() && $this->conn->next_result()) {
                if ($res = $this->conn->store_result()) { $res->free(); }
            }

            $stmt = $this->conn->prepare("CALL spGetProductosVisiblesVendedor(?)");
            if (!$stmt) {
                error_log("ProductoDAO::getProductosVisiblesVendedor - Error en prepare: " . $this->conn->error);
                return $productos;
            }
            $stmt->bind_param("i", $idVendedor);

            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                if ($resultado) {
                    while ($fila = $resultado->fetch_assoc()) {
                        $productos[] = $fila;
                    }
                    $resultado->free();
                }
            } else {
                error_log("ProductoDAO::getProductosVisiblesVendedor - Error en execute: " . $stmt->error);
            }
            $stmt->close();
            
            while ($this->conn->more_results() && $this->conn->next_result()) {
                if ($res = $this->conn->store_result()) { $res->free(); }
            }
            return $productos;
        }

        public function aumentarStockProducto($idProducto, $idVendedor, $cantidadAAgregar) {
        $response = ['status' => 'FAIL_UNKNOWN', 'message' => 'Error desconocido al actualizar stock.', 'nuevoStock' => null];
        
        // Limpiar resultados previos de la conexión si es necesario
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        // El SP ya no tiene el parámetro OUT
        $stmt = $this->conn->prepare("CALL spAumentarStockProducto(?, ?, ?)");
        if (!$stmt) {
            error_log("ProductoDAO::aumentarStockProducto - Error en prepare: " . $this->conn->error);
            return ['status' => 'FAIL_PREPARE', 'message' => 'Error interno del servidor (prepare).', 'nuevoStock' => null];
        }
        
        // Solo 3 parámetros IN
        $stmt->bind_param("iii", $idProducto, $idVendedor, $cantidadAAgregar);

        $executeSuccess = $stmt->execute();

        if (!$executeSuccess) {
            // Si execute() falla, $stmt->error debería tener el error de MySQL
            error_log("ProductoDAO::aumentarStockProducto - Error en execute: " . $stmt->error . " | MySQLi conn error: " . $this->conn->error);
            $error_message_from_stmt = $stmt->error;
            $stmt->close();
            // Limpiar la conexión después de un error
            while ($this->conn->more_results() && $this->conn->next_result()) {
                if ($res = $this->conn->store_result()) { $res->free(); }
            }
            return ['status' => 'FAIL_EXECUTE', 'message' => 'Error al ejecutar la actualización de stock: ' . $error_message_from_stmt, 'nuevoStock' => null];
        }

        $result = $stmt->get_result();
        if ($result) {
            $spResponse = $result->fetch_assoc();
            if ($spResponse) {
                // Asegurarse que los campos esperados estén presentes
                $response['status'] = $spResponse['status'] ?? 'FAIL_SP_RESPONSE_FORMAT';
                $response['message'] = $spResponse['message'] ?? 'Respuesta incompleta del servidor.';
                $response['nuevoStock'] = isset($spResponse['nuevoStock']) ? (int)$spResponse['nuevoStock'] : null;
            } else {
                 error_log("ProductoDAO::aumentarStockProducto - fetch_assoc devolvió null. El SP no retornó la fila esperada.");
                 $response = ['status' => 'FAIL_FETCH', 'message' => 'No se obtuvo respuesta del procedimiento.', 'nuevoStock' => null];
            }
            $result->free();
        } else {
            // Esto podría pasar si el SP no hizo un SELECT o hubo un error después de execute
            error_log("ProductoDAO::aumentarStockProducto - get_result() falló o no devolvió un resultset: " . $this->conn->error);
            $response = ['status' => 'FAIL_GET_RESULT', 'message' => 'Error al obtener la respuesta del servidor.', 'nuevoStock' => null];
        }
        
        $stmt->close();
        // Limpiar cualquier otro conjunto de resultados
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) { $res_extra->free(); }
        }
        
        return $response;
    }
}

?>