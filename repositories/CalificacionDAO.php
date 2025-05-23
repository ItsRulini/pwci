<?php
// repositories/CalificacionDAO.php
require_once '../connection/conexion.php'; 

class CalificacionDAO {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    /**
     * Guarda o actualiza la calificación y comentario de un producto por un usuario usando un SP.
     *
     * @param int $idUsuario
     * @param int $idProducto
     * @param int $calificacion (1-5, o 0 si solo hay comentario)
     * @param string|null $comentario
     * @param int|null $idTransaccion (Opcional)
     * @return array ['success' => bool, 'message' => string]
     */
    public function guardarCalificacionComentario($idUsuario, $idProducto, $calificacion, $comentario, $idTransaccion = null) {
            while ($this->conn->more_results() && $this->conn->next_result()) { 
                if ($res = $this->conn->store_result()) { $res->free(); }
            }

            // El SP spGuardarCalificacionComentario ahora usa p_idTransaccion activamente
            $stmt = $this->conn->prepare("CALL spGuardarCalificacionComentario(?, ?, ?, ?, ?)");
            if (!$stmt) {
                error_log("CalificacionDAO::guardarCalificacionComentario - Error en prepare: " . $this->conn->error);
                return ['success' => false, 'message' => 'Error al preparar la acción.'];
            }

            $calificacionInt = (int)$calificacion;
            $comentarioStr = is_null($comentario) ? '' : (string)$comentario;
            // Asegurarse que idTransaccion sea un entero para el bind_param, o null si no se pasa explícitamente (aunque el SP ahora lo requiere)
            $idTransaccionInt = ($idTransaccion === null || $idTransaccion === '') ? null : (int)$idTransaccion;
            
            // El SP espera 5 parámetros: idUsuario, idProducto, calificacion, comentario, idTransaccion
            $stmt->bind_param("iiisi", $idUsuario, $idProducto, $calificacionInt, $comentarioStr, $idTransaccionInt);
            
            $executeSuccess = $stmt->execute();
            if (!$executeSuccess) {
                error_log("CalificacionDAO::guardarCalificacionComentario - Error en execute: " . $stmt->error);
                $stmt->close();
                while ($this->conn->more_results() && $this->conn->next_result()) { if ($res = $this->conn->store_result()) { $res->free(); }}
                return ['success' => false, 'message' => 'Error al guardar la calificación/comentario.'];
            }

            $result = $stmt->get_result();
            $response = null;
            if ($result) {
                $response = $result->fetch_assoc();
                $result->free();
            }
            
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) { 
                if ($res_extra = $this->conn->store_result()) { $res_extra->free(); }
            }

            if ($response && isset($response['status']) && $response['status'] === 'SUCCESS') {
                return ['success' => true, 'message' => $response['message']];
            } else {
                return ['success' => false, 'message' => ($response['message'] ?? 'No se pudo guardar la calificación/comentario.')];
            }
        }
}
?>
