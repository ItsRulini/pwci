<?php
// repositories/TransaccionDAO.php
require_once '../connection/conexion.php'; // Ajusta la ruta si es necesario

class TransaccionDAO {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    /**
     * Obtiene el historial de compras de un usuario con filtros opcionales.
     *
     * @param int $idUsuario
     * @param int|null $idCategoriaFiltro 0 o null para todas.
     * @param string|null $fechaDesde Formato YYYY-MM-DD.
     * @param string|null $fechaHasta Formato YYYY-MM-DD.
     * @return array Lista de productos comprados.
     */
    public function obtenerHistorialCompras($idUsuario, $idCategoriaFiltro, $fechaDesde, $fechaHasta) {
        $historial = [];
        // Asegurar que los parámetros de fecha sean NULL si están vacíos
        $fechaDesde = empty($fechaDesde) ? null : $fechaDesde;
        $fechaHasta = empty($fechaHasta) ? null : $fechaHasta;
        $idCategoriaFiltro = ($idCategoriaFiltro === '' || $idCategoriaFiltro === 0) ? null : (int)$idCategoriaFiltro;


        // Limpiar resultados previos de la conexión
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }

        $stmt = $this->conn->prepare("CALL spObtenerHistorialComprasUsuario(?, ?, ?, ?)");
        if (!$stmt) {
            error_log("TransaccionDAO::obtenerHistorialCompras - Error en prepare: " . $this->conn->error);
            return $historial;
        }

        $stmt->bind_param("iiss", $idUsuario, $idCategoriaFiltro, $fechaDesde, $fechaHasta);
        
        if (!$stmt->execute()) {
            error_log("TransaccionDAO::obtenerHistorialCompras - Error en execute: " . $stmt->error);
            $stmt->close();
            return $historial;
        }

        $resultado = $stmt->get_result();
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $historial[] = $fila;
            }
            $resultado->free();
        } else {
            error_log("TransaccionDAO::obtenerHistorialCompras - Error al obtener resultado: " . $this->conn->error);
        }
        
        $stmt->close();
        
        // Limpiar cualquier otro conjunto de resultados
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res_extra = $this->conn->store_result()) {
                $res_extra->free();
            }
        }
        return $historial;
    }

    /**
     * Obtiene una lista de IDs de transacciones (compras) realizadas por un usuario.
     * Útil para poblar el dropdown de "Número de compra" para calificar.
     *
     * @param int $idUsuario
     * @return array Lista de objetos/arrays con idTransaccion y fechaTransaccion.
     */
    public function obtenerTransaccionesParaCalificar($idUsuario) {
        $transacciones = [];
        // Limpiar resultados previos
        while ($this->conn->more_results() && $this->conn->next_result()) { if ($res = $this->conn->store_result()) { $res->free(); }}

        // Podrías crear un SP específico o una consulta directa aquí.
        // Esta consulta obtiene las transacciones que aún podrían tener productos sin calificar.
        // O simplemente todas las transacciones del usuario.
        $query = "SELECT DISTINCT t.idTransaccion, t.fechaTransaccion
                  FROM Transaccion t
                  INNER JOIN Lista l ON t.idTransaccion = l.idLista
                  WHERE l.idUsuario = ? AND l.tipo = 'Carrito' AND l.estatusCompra = TRUE
                  ORDER BY t.fechaTransaccion DESC";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("TransaccionDAO::obtenerTransaccionesParaCalificar - Error en prepare: " . $this->conn->error);
            return $transacciones;
        }
        $stmt->bind_param("i", $idUsuario);
        if (!$stmt->execute()) {
            error_log("TransaccionDAO::obtenerTransaccionesParaCalificar - Error en execute: " . $stmt->error);
            $stmt->close();
            return $transacciones;
        }
        $resultado = $stmt->get_result();
        if($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $transacciones[] = $fila;
            }
            $resultado->free();
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) { if ($res = $this->conn->store_result()) { $res->free(); }}
        return $transacciones;
    }

    /**
     * Obtiene los productos de una transacción específica para que el usuario los califique.
     *
     * @param int $idTransaccion
     * @param int $idUsuario Para validar que la transacción pertenece al usuario.
     * @return array Lista de productos de la transacción.
     */
    public function obtenerProductosDeCompraParaCalificar($idTransaccion, $idUsuario) {
        $productos = [];
        while ($this->conn->more_results() && $this->conn->next_result()) { 
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spObtenerProductosDeCompraParaCalificar(?, ?)");
        if (!$stmt) {
            error_log("TransaccionDAO::obtenerProductosDeCompraParaCalificar - Error en prepare: " . $this->conn->error);
            return $productos; // Devuelve array vacío en error de preparación
        }
        
        $stmt->bind_param("ii", $idTransaccion, $idUsuario);
        
        if (!$stmt->execute()) {
            error_log("TransaccionDAO::obtenerProductosDeCompraParaCalificar - Error en execute: " . $stmt->error);
            $stmt->close();
            return $productos; // Devuelve array vacío en error de ejecución
        }
        
        $resultado = $stmt->get_result();
        if($resultado){
            while ($fila = $resultado->fetch_assoc()) {
                // Si el SP devuelve la fila de "error" (nombreProducto con mensaje), no la agregamos como producto válido.
                if (isset($fila['idProducto']) && $fila['idProducto'] !== null) {
                    $productos[] = $fila;
                } else {
                    // Podrías loguear el mensaje de error del SP aquí si lo deseas
                    error_log("SP spObtenerProductosDeCompraParaCalificar devolvió: " . ($fila['nombreProducto'] ?? 'Error desconocido del SP'));
                }
            }
            $resultado->free();
        } else {
            error_log("TransaccionDAO::obtenerProductosDeCompraParaCalificar - get_result() falló: " . $this->conn->error);
        }
        
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) { 
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        return $productos;
    }

    /**
     * Obtiene las ventas detalladas para un vendedor con filtros opcionales.
     *
     * @param int $idVendedor
     * @param int|null $idCategoriaFiltro 0 o null para todas.
     * @param string|null $fechaDesde Formato YYYY-MM-DD.
     * @param string|null $fechaHasta Formato YYYY-MM-DD.
     * @return array Lista de ventas detalladas.
     */
    public function obtenerVentasDetalladasVendedor($idVendedor, $idCategoriaFiltro, $fechaDesde, $fechaHasta) {
        $ventas = [];
        $fechaDesde = empty($fechaDesde) ? null : $fechaDesde;
        $fechaHasta = empty($fechaHasta) ? null : $fechaHasta;
        $idCategoriaFiltro = ($idCategoriaFiltro === '' || $idCategoriaFiltro === 0 || $idCategoriaFiltro === "0") ? null : (int)$idCategoriaFiltro;

        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spObtenerVentasDetalladasVendedor(?, ?, ?, ?)");
        if (!$stmt) {
            error_log("TransaccionDAO::obtenerVentasDetalladasVendedor - Error en prepare: " . $this->conn->error);
            return $ventas;
        }
        $stmt->bind_param("iiss", $idVendedor, $idCategoriaFiltro, $fechaDesde, $fechaHasta);
        
        if (!$stmt->execute()) {
            error_log("TransaccionDAO::obtenerVentasDetalladasVendedor - Error en execute: " . $stmt->error);
            $stmt->close(); return $ventas;
        }
        $resultado = $stmt->get_result();
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $ventas[] = $fila;
            }
            $resultado->free();
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        return $ventas;
    }

    /**
     * Obtiene las ventas agrupadas para un vendedor con filtros opcionales.
     *
     * @param int $idVendedor
     * @param int|null $idCategoriaFiltro 0 o null para todas.
     * @param string|null $fechaDesde Formato YYYY-MM-DD.
     * @param string|null $fechaHasta Formato YYYY-MM-DD.
     * @return array Lista de ventas agrupadas.
     */
    public function obtenerVentasAgrupadasVendedor($idVendedor, $idCategoriaFiltro, $fechaDesde, $fechaHasta) {
        $ventas = [];
        $fechaDesde = empty($fechaDesde) ? null : $fechaDesde;
        $fechaHasta = empty($fechaHasta) ? null : $fechaHasta;
        $idCategoriaFiltro = ($idCategoriaFiltro === '' || $idCategoriaFiltro === 0 || $idCategoriaFiltro === "0") ? null : (int)$idCategoriaFiltro;

        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spObtenerVentasAgrupadasVendedor(?, ?, ?, ?)");
        if (!$stmt) {
            error_log("TransaccionDAO::obtenerVentasAgrupadasVendedor - Error en prepare: " . $this->conn->error);
            return $ventas;
        }
        $stmt->bind_param("iiss", $idVendedor, $idCategoriaFiltro, $fechaDesde, $fechaHasta);

        if (!$stmt->execute()) {
            error_log("TransaccionDAO::obtenerVentasAgrupadasVendedor - Error en execute: " . $stmt->error);
            $stmt->close(); return $ventas;
        }
        $resultado = $stmt->get_result();
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $ventas[] = $fila;
            }
            $resultado->free();
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        return $ventas;
    }
}
?>
