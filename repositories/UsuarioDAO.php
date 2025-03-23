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
            } else {
                $userData = null;
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

            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Usuario registrado con éxito"]);
                return true;

            } else {
                echo json_encode(["success" => false, "message" => "Error en la ejecución: " . $stmt->error]);
            }

            $stmt->close();
            $this->conn->close();

        } catch (mysqli_sql_exception $e) {
            error_log("Error en loginUsuario: " . $e->getMessage()); // Loguear el error
            $userData = null;
        }

        return false;
    }
}


?>