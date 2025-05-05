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
            error_log("Error en loginUsuario: " . $e->getMessage()); // Loguear el error
        }

        return false;
    }

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

}


?>