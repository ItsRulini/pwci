<?php
class Categoria {
    private int $idCategoria;
    private string $nombre;
    private string $descripcion;
    private int $idCreador;

    public function __construct() {}

    public function getIdCategoria(): int {
        return $this->idCategoria;
    }
    public function setIdCategoria(int $idCategoria): void {
        $this->idCategoria = $idCategoria;
    }
    public function getNombre(): string {
        return $this->nombre;
    }
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }
    public function getDescripcion(): string {
        return $this->descripcion;
    }
    public function setDescripcion(string $descripcion): void {
        $this->descripcion = $descripcion;
    }
    public function getIdCreador(): int {
        return $this->idCreador;
    }
    public function setIdCreador(int $idCreador): void {
        $this->idCreador = $idCreador;
    }
}

?>