<?php
// repositories/ChatDAO.php

class ChatDAO {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerConversacionesUsuario($idUsuarioActual) {
        $conversaciones = [];
        $stmt = $this->conn->prepare("CALL spObtenerConversacionesUsuario(?)");
        if (!$stmt) {
            error_log("ChatDAO::obtenerConversacionesUsuario - Error en prepare: " . $this->conn->error);
            return $conversaciones;
        }
        $stmt->bind_param("i", $idUsuarioActual);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($row = $resultado->fetch_assoc()) {
            $conversaciones[] = $row;
        }
        $stmt->close();
        // Limpiar resultados
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }
        return $conversaciones;
    }
    
    public function insertarMensaje($tipo, $mensaje, $idRemitente, $idChat) {
        $outParamName = "@idNewMensaje_" . uniqid();
        $stmt = $this->conn->prepare("CALL spInsertarMensaje(?, ?, ?, ?, {$outParamName})");
        if (!$stmt) {
            error_log("ChatDAO::insertarMensaje - Error en prepare: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("ssii", $tipo, $mensaje, $idRemitente, $idChat);
        
        $executeSuccess = $stmt->execute();
        $stmt->close();

        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }

        if ($executeSuccess) {
            $result = $this->conn->query("SELECT {$outParamName} AS idMensaje");
            if ($result && $row = $result->fetch_assoc()) {
                $idMensajeCreado = $row['idMensaje'];
                $result->free();
                 while ($this->conn->more_results() && $this->conn->next_result()) { // Limpieza final
                    if ($res = $this->conn->store_result()) {
                        $res->free();
                    }
                }
                return $idMensajeCreado;
            } else {
                error_log("ChatDAO::insertarMensaje - Error al obtener {$outParamName}: " . $this->conn->error);
            }
        } else {
             error_log("ChatDAO::insertarMensaje - Error al ejecutar spInsertarMensaje.");
        }
        return null;
    }

    public function insertarOferta($idMensaje, $precio) {
        $stmt = $this->conn->prepare("CALL spInsertarOferta(?, ?)");
        if (!$stmt) {
            error_log("ChatDAO::insertarOferta - Error en prepare: " . $this->conn->error);
            return false;
        }
        // El SP spInsertarOferta espera (idMensaje, precio)
        // El tipo para precio debe ser 'd' (double)
        $stmt->bind_param("id", $idMensaje, $precio); 
        $success = $stmt->execute();
        if (!$success) {
            error_log("ChatDAO::insertarOferta - Error al ejecutar: " . $stmt->error);
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }
        return $success;
    }

    public function obtenerMensajesDeChat($idChat, $idUsuarioActual) {
        $mensajes = [];
        $stmt = $this->conn->prepare("CALL spObtenerMensajesDeChat(?)");
        if (!$stmt) {
            error_log("ChatDAO::obtenerMensajesDeChat - Error en prepare: " . $this->conn->error);
            return ['mensajes' => [], 'idUsuarioActual' => $idUsuarioActual];
        }
        $stmt->bind_param("i", $idChat);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($row = $resultado->fetch_assoc()) {
            // Determinar si el mensaje es del usuario actual
            $row['esMio'] = ($row['idRemitente'] == $idUsuarioActual);
            // Formatear hora (ejemplo simple, puedes usar DateTime para más control)
            $row['hora'] = date("H:i", strtotime($row['fechaEnvio']));
            $mensajes[] = $row;
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }
        // Devolvemos los mensajes y el idUsuarioActual para que el JS lo use
        return ['mensajes' => $mensajes, 'idUsuarioActual' => $idUsuarioActual];
    }

    // ... (tus métodos buscarChatExistente, crearChat, agregarUsuarioAlChat permanecen igual que en la respuesta anterior) ...
    public function buscarChatExistente($idUsuarioComprador, $idProducto) {
        $idChat = null;
        
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
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }

        if (!$idVendedor) {
            error_log("ChatDAO::buscarChatExistente - No se encontró vendedor para el producto ID: " . $idProducto);
            return null; 
        }

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
        
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_chat = $this->conn->store_result()) {
                $res_chat->free();
            }
        }
        return $idChat;
    }

    public function crearChat($idProducto) {
        $outParamName = "@idNewChat_" . uniqid(); 

        $stmt = $this->conn->prepare("CALL spCrearChat(?, " . $outParamName . ")");
        if (!$stmt) {
            error_log("ChatDAO::crearChat - Error en prepare spCrearChat: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $idProducto);
        $stmt->execute();
        $stmt->close();

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

        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) {
                $res_extra->free();
            }
        }
        
        return $idChatCreado;
    }

    public function agregarUsuarioAlChat($idChat, $idUsuario) {
        $stmt = $this->conn->prepare("CALL spAgregarUsuarioChat(?, ?)");
        
        if (!$stmt) {
            error_log("ChatDAO::agregarUsuarioAlChat - Error en prepare spAgregarUsuarioChat: " . $this->conn->error . " (ChatID: $idChat, UsuarioID: $idUsuario)");
            return false;
        }

        $stmt->bind_param("ii", $idChat, $idUsuario);
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("ChatDAO::agregarUsuarioAlChat - Error en execute spAgregarUsuarioChat: " . $stmt->error . " (ChatID: $idChat, UsuarioID: $idUsuario)");
        }
        
        $stmt->close();

        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($result_set = $this->conn->store_result()) {
                $result_set->free();
            }
        }
        return $resultado;
    }
}
?>
