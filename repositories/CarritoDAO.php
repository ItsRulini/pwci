<?php
require_once '../connection/conexion.php';

class CarritoDAO {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function agregarProductoAlCarrito($idUsuario, $idProducto) {
        try {
            // Limpiar resultados previos si los hubiera, antes de llamar a un SP
            while ($this->conn->more_results() && $this->conn->next_result()) {
                if ($res = $this->conn->store_result()) {
                    $res->free();
                }
            }
    
            $stmt = $this->conn->prepare("CALL spAgregarProductoAlCarrito(?, ?)");
            if (!$stmt) {
                error_log("CarritoDAO::agregarProductoAlCarrito - Error en prepare: " . $this->conn->error);
                return ["success" => false, "message" => "Error interno del servidor (prepare)."];
            }
            $stmt->bind_param("ii", $idUsuario, $idProducto);
    
            $executeSuccess = $stmt->execute();
    
            if (!$executeSuccess) {
                $error_msg = $stmt->error;
                $stmt->close();
                // Limpiar después de un execute fallido
                while ($this->conn->more_results() && $this->conn->next_result()) {
                    if ($res = $this->conn->store_result()) {
                        $res->free();
                    }
                }
                return ["success" => false, "message" => "Error al procesar la solicitud: " . $error_msg];
            }
    
            $result = $stmt->get_result();
            if (!$result) {
                $error_msg = $this->conn->error; 
                $stmt->close();
                while ($this->conn->more_results() && $this->conn->next_result()) {
                    if ($res = $this->conn->store_result()) {
                        $res->free();
                    }
                }
                return ["success" => false, "message" => "Error al obtener respuesta del servidor: " . $error_msg];
            }
            
            $response = $result->fetch_assoc();
            $result->free();
            $stmt->close();
    
            // Limpiar cualquier otro conjunto de resultados
            while ($this->conn->more_results() && $this->conn->next_result()) {
                if ($res_extra = $this->conn->store_result()) {
                    $res_extra->free();
                }
            }
    
            if ($response && isset($response['status'])) {
                // Convertir el status del SP a un booleano de 'success' para el frontend
                $isSuccess = (strpos($response['status'], 'SUCCESS') !== false);
                
                // Actualizar el idLista en la sesión si el SP lo devuelve (para el caso de creación de carrito)
                if ($isSuccess && isset($response['idLista']) && $response['idLista']) {
                    if (session_status() == PHP_SESSION_NONE) { // Asegurar que la sesión esté iniciada
                        session_start(); // Iniciar sesión si no está activa
                    }
                    // Solo actualiza si $_SESSION['idLista'] no está seteado o es diferente,
                    // para evitar sobreescribir innecesariamente si ya existe y es el mismo.
                    if (!isset($_SESSION['idLista']) || $_SESSION['idLista'] != $response['idLista']) {
                        $_SESSION['idLista'] = $response['idLista'];
                    }
                }
                return ["success" => $isSuccess, "message" => $response['message']];
            } else {
                return ["success" => false, "message" => "Respuesta inesperada del servidor."];
            }
    
        } catch (Exception $e) {
            error_log("CarritoDAO::agregarProductoAlCarrito - Excepción: " . $e->getMessage());
            return ["success" => false, "message" => "Excepción del servidor: " . $e->getMessage()];
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

    public function procesarCompra($idLista, $idUsuario) {
        $stmt = $this->conn->prepare("CALL spProcesarCompraYActualizarStock(?, ?)");
        if (!$stmt) {
            error_log("CarritoDAO::procesarCompra - Error en prepare: " . $this->conn->error);
            return ['status' => 'FAIL_PREPARE', 'message' => 'Error interno del servidor al procesar la compra (prepare).'];
        }

        $stmt->bind_param("ii", $idLista, $idUsuario);
        
        $executeSuccess = $stmt->execute();

        if (!$executeSuccess) {
            error_log("CarritoDAO::procesarCompra - Execute failed: " . $stmt->error);
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) { if ($res = $this->conn->store_result()) { $res->free(); }}
            return ['status' => 'FAIL_EXECUTE', 'message' => 'Error al procesar la compra.'];
        }

        $result = $stmt->get_result();
        if (!$result) {
            error_log("CarritoDAO::procesarCompra - get_result failed: " . $this->conn->error . " (stmt_error: " . $stmt->error . ")");
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) { if ($res = $this->conn->store_result()) { $res->free(); }}
            return ['status' => 'FAIL_GET_RESULT', 'message' => 'Error al obtener respuesta del servidor tras la compra.'];
        }
        
        $response = $result->fetch_assoc();
        $result->free();
        $stmt->close();
        
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) {
                $res_extra->free();
            }
        }

        if (!$response) {
            error_log("CarritoDAO::procesarCompra - fetch_assoc devolvió null.");
            return ['status' => 'FAIL_NO_RESPONSE_ROW', 'message' => 'Respuesta inesperada del servidor tras la compra.'];
        }
        
        return $response; // Debería ser ['status' => 'SUCCESS'/'FAIL_CART_INVALID', 'message' => '...', 'idListaComprada' => ...]
    }

    /**
     * Crea un nuevo carrito vacío para un usuario.
     * Esto es útil después de que un carrito ha sido comprado.
     *
     * @param int $idUsuario El ID del usuario.
     * @return int|null El ID del nuevo carrito creado, o null si falla.
     */
    public function crearNuevoCarritoVacioParaUsuario($idUsuario) {
        $nombreCarrito = "Carrito de usuario " . $idUsuario . " (" . date("Y-m-d H:i") . ")";
        $stmt = $this->conn->prepare("INSERT INTO Lista (tipo, nombre, privacidad, descripcion, idUsuario, estatusLista, estatusCompra) VALUES ('Carrito', ?, 'Privada', 'Nuevo carrito personal', ?, TRUE, FALSE)");
        if (!$stmt) {
            error_log("CarritoDAO::crearNuevoCarritoVacioParaUsuario - Error en prepare: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("si", $nombreCarrito, $idUsuario);
        if ($stmt->execute()) {
            $newIdLista = $this->conn->insert_id;
            $stmt->close();
            return $newIdLista;
        } else {
            error_log("CarritoDAO::crearNuevoCarritoVacioParaUsuario - Error al ejecutar insert: " . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function obtenerWishlistsPorUsuario($idUsuario) {
        $wishlists = [];
        // Limpiar resultados previos
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spGetWishlistsUsuario(?)");
        if (!$stmt) {
            error_log("CarritoDAO::obtenerWishlistsPorUsuario - Error en prepare: " . $this->conn->error);
            return $wishlists;
        }
        $stmt->bind_param("i", $idUsuario);
        
        if (!$stmt->execute()) {
            error_log("CarritoDAO::obtenerWishlistsPorUsuario - Error en execute: " . $stmt->error);
            $stmt->close();
            return $wishlists;
        }
        
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $wishlists[] = $row; // Contiene idLista, nombre, descripcion
            }
            $result->free();
        }
        $stmt->close();
        
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) { $res_extra->free(); }
        }
        return $wishlists;
    }

    /**
     * Crea una nueva wishlist para un usuario.
     *
     * @param int $idUsuario
     * @param string $nombre
     * @param string $descripcion
     * @param string $privacidad ('Privada' o 'Publica')
     * @return array Resultado con 'status', 'message', y 'idLista' si es exitoso.
     */
    public function crearWishlist($idUsuario, $nombre, $descripcion, $privacidad) {
        $response = ['status' => 'FAIL_UNKNOWN', 'message' => 'Error desconocido al crear wishlist.'];
        // Limpiar resultados previos
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        
        // Usar un nombre de variable de sesión único para el parámetro OUT
        $outParamName = "@idNewWishlist_" . uniqid();

        $stmt = $this->conn->prepare("CALL spCrearWishlist(?, ?, ?, ?, {$outParamName})");
        if (!$stmt) {
            error_log("CarritoDAO::crearWishlist - Error en prepare: " . $this->conn->error);
            return ['status' => 'FAIL_PREPARE', 'message' => 'Error interno (prepare).'];
        }

        $stmt->bind_param("isss", $idUsuario, $nombre, $descripcion, $privacidad);
        
        if (!$stmt->execute()) {
            error_log("CarritoDAO::crearWishlist - Error en execute: " . $stmt->error);
            $stmt->close();
            return ['status' => 'FAIL_EXECUTE', 'message' => 'Error al ejecutar la creación de la wishlist.'];
        }
        
        // El SP spCrearWishlist ahora devuelve un SELECT con status, message, idLista
        $result = $stmt->get_result();
        if ($result) {
            $spResponse = $result->fetch_assoc();
            if ($spResponse) {
                $response = $spResponse; // Contendrá status, message, idLista
            }
            $result->free();
        } else {
             error_log("CarritoDAO::crearWishlist - No se pudo obtener resultado del SP: " . $this->conn->error);
             $response = ['status' => 'FAIL_SP_RESULT', 'message' => 'No se obtuvo respuesta del servidor.'];
        }
        
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) { $res_extra->free(); }
        }
        return $response;
    }

    public function getProductosDeLista($idLista, $idUsuario) {
        $productos = [];
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spGetProductosDeLista(?, ?)");
        if (!$stmt) {
            error_log("CarritoDAO::getProductosDeLista - Error en prepare: " . $this->conn->error);
            return $productos;
        }
        $stmt->bind_param("ii", $idLista, $idUsuario);
        
        if (!$stmt->execute()) {
            error_log("CarritoDAO::getProductosDeLista - Error en execute: " . $stmt->error);
            $stmt->close();
            return $productos;
        }
        
        $resultado = $stmt->get_result();
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                // Si el SP devuelve la fila de error, idProducto será NULL
                if (isset($fila['idProducto']) && $fila['idProducto'] !== null) {
                    $productos[] = $fila;
                } else if (isset($fila['nombre'])) { // Para capturar el mensaje de error del SP
                    error_log("CarritoDAO::getProductosDeLista - SP Mensaje: " . $fila['nombre']);
                }
            }
            $resultado->free();
        }
        $stmt->close();
        
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) { $res_extra->free(); }
        }
        return $productos;
    }

    public function eliminarWishlist($idLista, $idUsuario) {
        $response = ['status' => 'FAIL_UNKNOWN', 'message' => 'Error desconocido al eliminar wishlist.'];
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spEliminarWishlist(?, ?)");
        if (!$stmt) {
            error_log("CarritoDAO::eliminarWishlist - Error en prepare: " . $this->conn->error);
            return ['status' => 'FAIL_PREPARE', 'message' => 'Error interno (prepare).'];
        }
        $stmt->bind_param("ii", $idLista, $idUsuario);

        if (!$stmt->execute()) {
            error_log("CarritoDAO::eliminarWishlist - Error en execute: " . $stmt->error);
            $stmt->close();
            return ['status' => 'FAIL_EXECUTE', 'message' => 'Error al ejecutar la eliminación.'];
        }

        $result = $stmt->get_result();
        if ($result) {
            $spResponse = $result->fetch_assoc();
            if ($spResponse) {
                $response = $spResponse;
            }
            $result->free();
        } else {
            error_log("CarritoDAO::eliminarWishlist - No se pudo obtener resultado del SP: " . $this->conn->error);
        }
        
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) { $res_extra->free(); }
        }
        return $response;
    }

    public function actualizarWishlist($idLista, $idUsuario, $nuevoNombre, $nuevaDescripcion, $nuevaPrivacidad) {
        $response = ['status' => 'FAIL_UNKNOWN', 'message' => 'Error desconocido al actualizar wishlist.'];
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spActualizarWishlist(?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("CarritoDAO::actualizarWishlist - Error en prepare: " . $this->conn->error);
            return ['status' => 'FAIL_PREPARE', 'message' => 'Error interno (prepare).'];
        }
        $stmt->bind_param("iisss", $idLista, $idUsuario, $nuevoNombre, $nuevaDescripcion, $nuevaPrivacidad);

        if (!$stmt->execute()) {
            error_log("CarritoDAO::actualizarWishlist - Error en execute: " . $stmt->error);
            $stmt->close();
            return ['status' => 'FAIL_EXECUTE', 'message' => 'Error al ejecutar la actualización.'];
        }

        $result = $stmt->get_result();
        if ($result) {
            $spResponse = $result->fetch_assoc();
            if ($spResponse) {
                $response = $spResponse;
            }
            $result->free();
        } else {
            error_log("CarritoDAO::actualizarWishlist - No se pudo obtener resultado del SP: " . $this->conn->error);
        }
        
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) { $res_extra->free(); }
        }
        return $response;
    }

    public function eliminarProductoDeWishlist($idLista, $idProducto, $idUsuario) {
        $response = ['status' => 'FAIL_UNKNOWN', 'message' => 'Error desconocido al eliminar producto de la wishlist.'];
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spEliminarProductoDeWishlist(?, ?, ?)");
        if (!$stmt) {
            error_log("CarritoDAO::eliminarProductoDeWishlist - Error en prepare: " . $this->conn->error);
            return ['status' => 'FAIL_PREPARE', 'message' => 'Error interno (prepare).'];
        }
        $stmt->bind_param("iii", $idLista, $idProducto, $idUsuario);

        if (!$stmt->execute()) {
            error_log("CarritoDAO::eliminarProductoDeWishlist - Error en execute: " . $stmt->error);
            $stmt->close();
            return ['status' => 'FAIL_EXECUTE', 'message' => 'Error al ejecutar la eliminación del producto.'];
        }

        $result = $stmt->get_result();
        if ($result) {
            $spResponse = $result->fetch_assoc();
            if ($spResponse) {
                $response = $spResponse;
            }
            $result->free();
        } else {
            error_log("CarritoDAO::eliminarProductoDeWishlist - No se pudo obtener resultado del SP: " . $this->conn->error);
        }
        
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) { $res_extra->free(); }
        }
        return $response;
    }

    public function agregarProductoAWishlist($idLista, $idProducto, $idUsuario) {
        $response = ['status' => 'FAIL_UNKNOWN', 'message' => 'Error desconocido al agregar a wishlist.'];
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spAgregarProductoAWishlist(?, ?, ?)");
        if (!$stmt) {
            error_log("CarritoDAO::agregarProductoAWishlist - Error en prepare: " . $this->conn->error);
            return ['status' => 'FAIL_PREPARE', 'message' => 'Error interno (prepare).'];
        }
        $stmt->bind_param("iii", $idLista, $idProducto, $idUsuario);

        if (!$stmt->execute()) {
            error_log("CarritoDAO::agregarProductoAWishlist - Error en execute: " . $stmt->error);
            $stmt->close();
            return ['status' => 'FAIL_EXECUTE', 'message' => 'Error al ejecutar la adición a wishlist.'];
        }

        $result = $stmt->get_result();
        if ($result) {
            $spResponse = $result->fetch_assoc();
            if ($spResponse) {
                $response = $spResponse;
            }
            $result->free();
        } else {
            error_log("CarritoDAO::agregarProductoAWishlist - No se pudo obtener resultado del SP: " . $this->conn->error);
        }
        
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) { $res_extra->free(); }
        }
        return $response;
    }

    public function getWishlistsPublicasDeUsuario($idUsuarioConsultado) {
            $wishlists = [];
            while ($this->conn->more_results() && $this->conn->next_result()) {
                if ($res = $this->conn->store_result()) { $res->free(); }
            }

            $stmt = $this->conn->prepare("CALL spGetWishlistsPublicasDeUsuario(?)");
            if (!$stmt) {
                error_log("CarritoDAO::getWishlistsPublicasDeUsuario - Error en prepare: " . $this->conn->error);
                return $wishlists;
            }
            $stmt->bind_param("i", $idUsuarioConsultado);

            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                if ($resultado) {
                    while ($row = $resultado->fetch_assoc()) {
                        $wishlists[] = $row;
                    }
                    $resultado->free();
                }
            } else {
                error_log("CarritoDAO::getWishlistsPublicasDeUsuario - Error en execute: " . $stmt->error);
            }
            $stmt->close();
            
            while ($this->conn->more_results() && $this->conn->next_result()) {
                if ($res = $this->conn->store_result()) { $res->free(); }
            }
            return $wishlists;
        }
}
?>
