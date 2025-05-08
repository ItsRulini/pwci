<?php
require_once '../connection/conexion.php';
require_once '../models/Categoria.php';

class CategoriaDAO {
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function insertCategoria($categoria): bool
    {
        try {
            // Llamada al procedimiento almacenado
            $sql = "CALL spInsertCategoria(?, ?, ?)";
            $stmt = $this->conn->prepare($sql);

            $nombre = $categoria->getNombre();
            $descripcion = $categoria->getDescripcion();
            $creador = $categoria->getIdCreador();

            // Asociar parámetros desde el objeto Usuario
            $stmt->bind_param(
                "sss",
                $nombre,
                $descripcion,
                $creador,
            );

            if ($stmt->execute()) {
                $stmt->close();
                return true;
            } else {
                $stmt->close();
                return false;
            }

        } catch (mysqli_sql_exception $e) {
            error_log("Error en insertCategoria: " . $e->getMessage()); // Loguear el error
        }

        return false;
    }
}


?>