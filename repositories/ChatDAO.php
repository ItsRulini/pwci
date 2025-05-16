<?php
class ChatDAO {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function insertarMensaje($tipo, $mensaje, $idRemitente, $idChat) {
        $stmt = $this->conn->prepare("CALL spInsertarMensaje(?, ?, ?, ?, @idMensaje)");
        $stmt->bind_param("ssii", $tipo, $mensaje, $idRemitente, $idChat);
        if ($stmt->execute()) {
            $result = $this->conn->query("SELECT @idMensaje AS idMensaje");
            $row = $result->fetch_assoc();
            return $row['idMensaje'] ?? null;
        }
        return null;
    }

    public function insertarOferta($idMensaje, $precio) {
        $stmt = $this->conn->prepare("CALL spInsertarOferta(?, ?)");
        $stmt->bind_param("id", $idMensaje, $precio);
        $stmt->execute();
        $stmt->close();
    }

    public function obtenerMensajesDeChat($idChat) {
        $stmt = $this->conn->prepare("CALL spObtenerMensajesDeChat(?)");
        $stmt->bind_param("i", $idChat);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $mensajes = [];
        while ($row = $resultado->fetch_assoc()) {
            $mensajes[] = $row;
        }

        $stmt->close();
        return $mensajes;
    }

    public function buscarChatExistente($idUsuarioComprador, $idProducto) {
        $idChat = null;
        
        // Primero, obtenemos el idVendedor del producto.
        $idVendedor = null;
        $stmtVendedor = $this->conn->prepare("SELECT idVendedor FROM Producto WHERE idProducto = ?");
        if (!$stmtVendedor) {
            error_log("ChatDAO::buscarChatExistente - Error al preparar consulta de vendedor: " . $this->conn->error);
            return null;
        }
        $stmtVendedor->bind_param("i", $idProducto);
        $stmtVendedor->execute();
        $resultVendedor = $stmtVendedor->get_result();
        if ($rowVendedor = $resultVendedor->fetch_assoc()) {
            $idVendedor = $rowVendedor['idVendedor'];
        }
        $stmtVendedor->close();
        // Limpiar resultados de la consulta del vendedor
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }

        if (!$idVendedor) {
            error_log("ChatDAO::buscarChatExistente - No se encontró vendedor para el producto ID: " . $idProducto);
            return null; // No se puede buscar chat si no hay vendedor
        }

        // Ahora buscamos el chat que involucre al producto, al comprador y al vendedor.
        // Un chat es único para un producto y la pareja comprador-vendedor.
        $stmt = $this->conn->prepare(
            "SELECT c.idChat 
             FROM Chat c
             INNER JOIN Chat_Usuario cu_comprador ON c.idChat = cu_comprador.idChat AND cu_comprador.idUsuario = ?
             INNER JOIN Chat_Usuario cu_vendedor ON c.idChat = cu_vendedor.idChat AND cu_vendedor.idUsuario = ?
             WHERE c.idProducto = ?
             LIMIT 1"
        );
        
        if (!$stmt) {
            error_log("ChatDAO::buscarChatExistente - Error en prepare: " . $this->conn->error);
            return null;
        }

        $stmt->bind_param("iii", $idUsuarioComprador, $idVendedor, $idProducto);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $idChat = (int)$row['idChat'];
        }
        
        $stmt->close();
        
        // Limpiar resultados de la búsqueda del chat
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_chat = $this->conn->store_result()) {
                $res_chat->free();
            }
        }
        return $idChat;
    }

    /**
     * Crea un nuevo chat para un producto y devuelve su ID.
     *
     * @param int $idProducto El ID del producto.
     * @return int|null El ID del chat creado, o null si falla.
     */
    public function crearChat($idProducto) {
        // Usar un nombre de variable de sesión único para el parámetro OUT
        $outParamName = "@idNewChat_" . uniqid(); 

        $stmt = $this->conn->prepare("CALL spCrearChat(?, " . $outParamName . ")");
        if (!$stmt) {
            error_log("ChatDAO::crearChat - Error en prepare spCrearChat: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $idProducto);
        $stmt->execute();
        $stmt->close();

        // Limpiar resultados DESPUÉS de CALL y ANTES de SELECT @variable
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($result_set = $this->conn->store_result()) {
                $result_set->free();
            }
        }

        $result = $this->conn->query("SELECT " . $outParamName . " AS idChat");
        if (!$result) {
            error_log("ChatDAO::crearChat - Error en SELECT " . $outParamName . ": " . $this->conn->error);
            return null;
        }
        $row = $result->fetch_assoc();
        $idChatCreado = isset($row['idChat']) ? (int)$row['idChat'] : null;
        $result->free();

        // Limpiar cualquier otro posible resultado (aunque después de un SELECT simple, no debería haber más)
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) {
                $res_extra->free();
            }
        }
        
        return $idChatCreado;
    }

    /**
     * Agrega un usuario a un chat existente.
     * Usa INSERT IGNORE, por lo que no falla si el usuario ya está en el chat.
     *
     * @param int $idChat El ID del chat.
     * @param int $idUsuario El ID del usuario a agregar.
     * @return bool True si la ejecución fue exitosa (no necesariamente si se insertó una nueva fila).
     */
    public function agregarUsuarioAlChat($idChat, $idUsuario) {
        $stmt = $this->conn->prepare("CALL spAgregarUsuarioChat(?, ?)");
        
        if (!$stmt) {
            // El error que reportaste ("Error en prepare: Commands out of sync...")
            // Si ocurre aquí, significa que la operación ANTERIOR a esta llamada no limpió sus resultados.
            error_log("ChatDAO::agregarUsuarioAlChat - Error en prepare spAgregarUsuarioChat: " . $this->conn->error . " (ChatID: $idChat, UsuarioID: $idUsuario)");
            return false;
        }

        $stmt->bind_param("ii", $idChat, $idUsuario);
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("ChatDAO::agregarUsuarioAlChat - Error en execute spAgregarUsuarioChat: " . $stmt->error . " (ChatID: $idChat, UsuarioID: $idUsuario)");
        }
        
        $stmt->close();

        // Limpiar resultados después de CALL
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($result_set = $this->conn->store_result()) {
                $result_set->free();
            }
        }
        return $resultado;
    }

}

?>