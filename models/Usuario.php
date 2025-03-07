<?php
class Usuario {
    private int $idUsuario;
    private string $nombreUsuario;
    private string $email;
    private string $contraseña;
    private string $nombres;
    private string $paterno;
    private string $materno;
    private ?string $fotoAvatar; // Puede ser NULL, por eso usamos '?'
    private DateTime $fechaNacimiento;
    private string $genero;
    private DateTime $fechaRegistro;
    private string $rol;

    private string $privacidad;

    public function __construct() {}

    public function getIdUsuario(): int {
        return $this->idUsuario;
    }
    public function setIdUsuario(int $idUsuario): void {
        $this->idUsuario = $idUsuario;
    }
    public function getNombreUsuario(): string {
        return $this->nombreUsuario;
    }
    public function setNombreUsuario(string $nombreUsuario): void {
        $this->nombreUsuario = $nombreUsuario;
    }
    public function getEmail(): string {
        return $this->email;
    }
    public function setEmail(string $email): void { 
        $this->email = $email;
    }
    public function getContraseña(): string {
        return $this->contraseña;
    }
    public function setContraseña(string $contraseña): void {
        $this->contraseña = $contraseña;
    }
    public function getNombres(): string {
        return $this->nombres;
    }
    public function setNombres(string $nombres): void {
        $this->nombres = $nombres;
    }
    public function getPaterno(): string {
        return $this->paterno;
    }
    public function setPaterno(string $paterno): void {
        $this->paterno = $paterno;
    }
    public function getMaterno(): string {
        return $this->materno;
    }
    public function setMaterno($materno): void {
        $this->materno = $materno;
    }
    public function getFotoAvatar(): ?string {
        return $this->fotoAvatar;
    }
    public function setFotoAvatar(?string $fotoAvatar): void {
        $this->fotoAvatar = $fotoAvatar;
    }
    public function getFechaNacimiento(): string {
        return $this->fechaNacimiento->format('Y-m-d');  // Formato estándar de SQL
    }
    public function setFechaNacimiento(string $fechaNacimientoFormato): void {
        $fecha = new DateTime( $fechaNacimientoFormato);
        $this->fechaNacimiento = $fecha;
    }

    public function getGenero(): string {
        return $this->genero;
    }
    public function setGenero(string $genero): void {
        $this->genero = $genero;
    }

    public function getFechaRegistro(): string {
        return $this->fechaRegistro->format('Y-m-d H:i:s');  // Con hora incluida
    }
    public function setFechaRegistro(string $fechaRegistro): void {
        $fecha = new DateTime( $fechaRegistro );
        $this->fechaRegistro = $fecha;
    }
    public function getRol(): string {
        return $this->rol;
    }
    public function setRol(string $rol): void {
        $this->rol = $rol;
    }

    public function getPrivacidad(): string {
        return $this->privacidad;
    }
    public function setPrivacidad(string $privacidad): void {
        $this->privacidad = $privacidad;
    }
}

?>