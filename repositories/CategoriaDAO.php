<?php
require_once '../connection/conexion.php'; 
require_once '../models/Categoria.php';   
require_once '../models/Usuario.php';    

class CategoriaDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function insertarCategoria(Categoria $categoria): bool {
        try {
            $sql = "CALL spInsertCategoria(?, ?, ?)";
            $stmt = $this->conn->prepare($sql);

            if ($stmt === false) {
                error_log("Error en la preparación de spInsertCategoria: " . $this->conn->error);
                return false;
            }

            $nombre = $categoria->getNombre();
            $descripcion = $categoria->getDescripcion();
            $idCreador = $categoria->getIdCreador();

            $stmt->bind_param("ssi", $nombre, $descripcion, $idCreador);

            if ($stmt->execute()) {
                $stmt->close();
                // Limpiar resultados múltiples si el SP los genera
                while ($this->conn->more_results() && $this->conn->next_result()) {;}
                return true;
            } else {
                error_log("Error en la ejecución de spInsertCategoria: " . $stmt->error);
                $stmt->close();
                return false;
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Excepción en insertarCategoria: " . $e->getMessage());
            return false;
        }
        //return false;
    }

    public function getCategorias(): array {
        $categorias = [];
        try {
            // Corregido el SELECT en el SP (mentalmente, el SP original tenía 'idCategoria nombre')
            // El SP debe ser: SELECT idCategoria, nombre, descripcion FROM Categoria;
            $sql = "CALL spGetCategorias()";
            $stmt = $this->conn->prepare($sql);

            if ($stmt === false) {
                error_log("Error en la preparación de spGetCategorias: " . $this->conn->error);
                return $categorias;
            }

            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    $categoria = new Categoria();
                    $categoria->setIdCategoria($fila["idCategoria"]); // Asegúrate que el SP devuelve 'idCategoria'
                    $categoria->setNombre($fila["nombre"]);
                    $categoria->setDescripcion($fila["descripcion"]);
                    // Si tu SP spGetCategorias devuelve más campos (idCreador, fechaCreacion), mapealos aquí.
                    $categorias[] = $categoria;
                }
            }
            $stmt->close();
            // Limpiar resultados múltiples si el SP los genera
            while ($this->conn->more_results() && $this->conn->next_result()) {;}

        } catch (mysqli_sql_exception $e) {
            error_log("Excepción en getCategorias: " . $e->getMessage());
        }
        return $categorias;
    }
}
?>