<?php
require_once '../connection/conexion.php';
require_once '../models/Usuario.php';

class UsuarioDAO
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function loginUsuario($usuario, $pass)
    {
        $userData = null;
        try {
            // Llamada al procedimiento almacenado
            $sql = "CALL spLogin(?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $usuario, $pass);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $fila = $resultado->fetch_assoc();

                if (isset($fila['mensaje'])) {
                    // El procedimiento devolvió un error
                    $userData = $fila['mensaje']; // Aquí devolvemos el mensaje de error ("Usuario dado de baja" o "Credenciales incorrectas")
                } else {
                    // El procedimiento devolvió los datos del usuario
                    $userData = new Usuario();
                    $userData->setIdUsuario($fila["idUsuario"]);
                    $userData->setNombreUsuario($fila["nombreUsuario"]);
                    $userData->setContraseña($fila["contraseña"]);
                    $userData->setEmail($fila["email"]);
                    $userData->setFotoAvatar($fila["fotoAvatar"]);
                    $userData->setNombres($fila["nombres"]);
                    $userData->setPaterno($fila["paterno"]);
                    $userData->setMaterno($fila["materno"]);
                    $userData->setFechaNacimiento($fila["fechaNacimiento"]);
                    $userData->setRol($fila["rol"]);
                    $userData->setGenero($fila["genero"]);
                    $userData->setPrivacidad($fila["privacidad"]);
                }
            } else {
                $userData = "Credenciales incorrectas"; // No hay ningún registro
            }
            $stmt->close();

        } catch (mysqli_sql_exception $e) {
            error_log("Error en loginUsuario: " . $e->getMessage()); // Loguear el error
            $userData = null;
        }

        return $userData;
    }

    public function registrarUsuario($usuario): bool
    {
        try {
            // Llamada al procedimiento almacenado
            $sql = "CALL spInsertUsuario(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);

            if ($stmt === false) {
                die(json_encode(["success" => false, "message" => "Error en la preparación de la consulta: " . $this->conn->error]));
            }

            $rol = $usuario->getRol();
            $user = $usuario->getNombreUsuario();
            $pass = $usuario->getContraseña();
            $email = $usuario->getEmail();
            $nombres = $usuario->getNombres();
            $paterno = $usuario->getPaterno();
            $materno = $usuario->getMaterno();
            $avatar = $usuario->getFotoAvatar();
            $genero = $usuario->getGenero();
            $nacimiento = $usuario->getFechaNacimiento();

            // Asociar parámetros desde el objeto Usuario
            $stmt->bind_param(
                "ssssssssss",
                $rol,
                $user,
                $pass,
                $email,
                $nombres,
                $paterno,
                $materno,
                $avatar,
                $genero,
                $nacimiento
            );

            if ($stmt->execute()) {
                $stmt->close();
                return true;
            } else {
                $stmt->close();
                return false;
            }

        } catch (mysqli_sql_exception $e) {
            error_log("Error en registroUsuario: " . $e->getMessage()); // Loguear el error
        }

        return false;
    }

    public function actualizarUsuario($idUsuario, $nombreUsuario, $email, $nombres, $paterno, $materno, $fotoAvatar, $fechaNacimiento, $privacidad, $nuevaContraseña = null) {
        // Limpiar resultados previos
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spUpdateUsuario(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("UsuarioDAO::actualizarUsuario - Error en prepare: " . $this->conn->error);
            return false; // O un array con error
        }
        // Tipos: i, s, s, s, s, s, s, s, s, s
        $stmt->bind_param("isssssssss", $idUsuario, $nombreUsuario, $email, $nombres, $paterno, $materno, $fotoAvatar, $fechaNacimiento, $privacidad, $nuevaContraseña);
        
        $executeSuccess = $stmt->execute();
        
        if (!$executeSuccess) {
            error_log("UsuarioDAO::actualizarUsuario - Error en execute: " . $stmt->error);
            $stmt->close();
            return false;
        }

        // El SP ahora devuelve un SELECT con status y message
        $result = $stmt->get_result();
        $response = null;
        if ($result) {
            $response = $result->fetch_assoc();
            $result->free();
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        // Devolver true si el SP indicó SUCCESS, incluso si no hubo cambios (SUCCESS_NO_CHANGE)
        return ($response && isset($response['status']) && strpos($response['status'], 'SUCCESS') !== false);
    }

    // Necesitas estos métodos para la validación AJAX si no los tienes ya
    // y que puedan ignorar el ID del usuario actual

    public function validarEmailExistente($email, $idUsuarioActualAIgnorar = 0) {
        while ($this->conn->more_results() && $this->conn->next_result()) { if ($res = $this->conn->store_result()) { $res->free(); }}
        $stmt = $this->conn->prepare("SELECT idUsuario FROM Usuario WHERE email = ? AND idUsuario != ?");
        $stmt->bind_param("si", $email, $idUsuarioActualAIgnorar);
        $stmt->execute();
        $result = $stmt->get_result();
        $existe = $result->num_rows > 0;
        $result->free();
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) { if ($res = $this->conn->store_result()) { $res->free(); }}
        return $existe; // true si existe (y no es el usuario actual), false si no
    }

    public function validarNombreUsuarioExistente($nombreUsuario, $idUsuarioActualAIgnorar = 0) {
        while ($this->conn->more_results() && $this->conn->next_result()) { if ($res = $this->conn->store_result()) { $res->free(); }}
        $stmt = $this->conn->prepare("SELECT idUsuario FROM Usuario WHERE nombreUsuario = ? AND idUsuario != ?");
        $stmt->bind_param("si", $nombreUsuario, $idUsuarioActualAIgnorar);
        $stmt->execute();
        $result = $stmt->get_result();
        $existe = $result->num_rows > 0;
        $result->free();
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) { if ($res = $this->conn->store_result()) { $res->free(); }}
        return $existe; // true si existe (y no es el usuario actual), false si no
    }
    // public function actualizarUsuario($idUsuario, $nombreUsuario, $email, $nombres, $paterno, $materno, $fotoAvatar, $fechaNacimiento): bool
    // {
    //     try {
    //         $sql = "CALL spUpdateUsuario(?, ?, ?, ?, ?, ?, ?, ?)";
    //         $stmt = $this->conn->prepare($sql);

    //         if ($stmt === false) {
    //             error_log("Error en la preparación de spUpdateUsuario: " . $this->conn->error);
    //             return false;
    //         }

    //         $stmt->bind_param(
    //             "isssssss", // i para integer (idUsuario), s para strings
    //             $idUsuario,
    //             $nombreUsuario,
    //             $email,
    //             $nombres,
    //             $paterno,
    //             $materno,
    //             $fotoAvatar, // Este es el nombre del archivo de la imagen
    //             $fechaNacimiento
    //         );

    //         if ($stmt->execute()) {
    //             $stmt->close();
    //             // Limpiar cualquier resultado múltiple pendiente si el SP los genera
    //             while ($this->conn->more_results() && $this->conn->next_result()) {
    //                 // Descartar resultados adicionales
    //             }
    //             return true;
    //         } else {
    //             error_log("Error en la ejecución de spUpdateUsuario: " . $stmt->error);
    //             $stmt->close();
    //             return false;
    //         }
    //     } catch (mysqli_sql_exception $e) {
    //         error_log("Excepción en actualizarUsuario: " . $e->getMessage());
    //         return false;
    //     }
    //     // Asegurarse de que siempre se retorne un booleano en caso de flujos inesperados
    //     //return false;
    // }

    public function validarCorreo($email): bool
    {
        try {
            // Llamada al procedimiento almacenado
            $sql = "CALL spValidarEmailExistente(?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            $existe = ($stmt->num_rows > 0) ? false : true;

            $stmt->free_result();
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) {
            }

            return $existe;

        } catch (mysqli_sql_exception $e) {
            error_log("Error en validarCorreo: " . $e->getMessage()); // Loguear el error
        }

        return false;

    }

    public function validarUsuario($usuario): bool
    {
        try {
            // Llamada al procedimiento almacenado
            $sql = "CALL spValidarUsuarioExistente(?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $stmt->store_result(); // <<<<<< CAMBIO AQUÍ

            $existe = ($stmt->num_rows > 0) ? false : true; // <<<<< TAMBIÉN CAMBIO

            $stmt->free_result(); // <<<<<
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) {
            }

            return $existe;

        } catch (mysqli_sql_exception $e) {
            error_log("Error en validarUsuario: " . $e->getMessage()); // Loguear el error
        }

        return false;

    }
    public function getUsuariosRegistrados()
    {
        $usuarios = array();
        try {
            // Llamada al procedimiento almacenado
            $sql = "CALL spGetUsuarios()";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    $usuario = new Usuario();
                    $usuario->setIdUsuario($fila["idUsuario"]);
                    $usuario->setNombreUsuario($fila["nombreUsuario"]);
                    $usuario->setEmail($fila["email"]);
                    $usuario->setRol($fila["rol"]);
                    $usuario->setFechaRegistro($fila["fechaRegistro"]);
                    $usuario->setEstatus($fila["estatus"]);

                    array_push($usuarios, $usuario);
                }
            } else {
                return null;
            }
            $stmt->close();

        } catch (mysqli_sql_exception $e) {
            error_log("Error en getUsuariosRegistrados: " . $e->getMessage()); // Loguear el error
            return null;
        }

        return $usuarios;
    }

    public function obtenerPerfilesSocial($idUsuarioActual) {
        $perfiles = [];
        // Limpiar resultados previos de la conexión si es necesario
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }

        $stmt = $this->conn->prepare("CALL spObtenerPerfilesSocial(?)");
        if (!$stmt) {
            error_log("UsuarioDAO::obtenerPerfilesSocial - Error en prepare: " . $this->conn->error);
            return $perfiles;
        }
        $stmt->bind_param("i", $idUsuarioActual);

        if (!$stmt->execute()) {
            error_log("UsuarioDAO::obtenerPerfilesSocial - Error en execute: " . $stmt->error);
            $stmt->close();
            return $perfiles;
        }

        $resultado = $stmt->get_result();
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                // Puedes devolver arrays asociativos directamente
                // o crear objetos Usuario simplificados si lo prefieres.
                $perfiles[] = $fila;
            }
            $resultado->free();
        }
        $stmt->close();
        
        while ($this->conn->more_results() && $this->conn->next_result()) { // Limpieza final
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        return $perfiles;
    }

    public function getDetallesPerfilExterno($idUsuarioConsultado) {
        $perfil = null;
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        $stmt = $this->conn->prepare("CALL spGetDetallesPerfilExterno(?)");
        if (!$stmt) {
            error_log("UsuarioDAO::getDetallesPerfilExterno - Error en prepare: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $idUsuarioConsultado);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            if ($resultado) {
                $perfil = $resultado->fetch_assoc(); // Solo esperamos una fila
                $resultado->free();
            }
        } else {
            error_log("UsuarioDAO::getDetallesPerfilExterno - Error en execute: " . $stmt->error);
        }
        $stmt->close();
        
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($res = $this->conn->store_result()) { $res->free(); }
        }
        return $perfil;
    }

}


?>