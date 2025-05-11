<?php
class Producto {
    private ?int $idProducto = null;
    private string $nombre;
    private ?string $descripcion = null;
    private string $tipo; // 'Venta' o 'Cotizacion'
    private ?float $precio = null;
    private ?int $stock = 0;
    private ?string $fechaAlta = null;
    private string $estatus = 'pendiente'; // 'pendiente', 'aceptado', 'rechazado'
    private int $idVendedor;
    private ?int $idAdministrador = null;
    
    private array $idCategorias = []; // Array de IDs de categorías seleccionadas
    private array $archivosMultimediaNombres = []; // Array de nombres de archivo para multimedia (subidos)

    public function __construct() {}

    // Getters
    public function getIdProducto(): ?int { return $this->idProducto; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getTipo(): string { return $this->tipo; }
    public function getPrecio(): ?float { return $this->precio; }
    public function getStock(): ?int { return $this->stock; }
    public function getFechaAlta(): ?string { return $this->fechaAlta; }
    public function getEstatus(): string { return $this->estatus; }
    public function getIdVendedor(): int { return $this->idVendedor; }
    public function getIdAdministrador(): ?int { return $this->idAdministrador; }
    public function getIdCategorias(): array { return $this->idCategorias; } // Para las categorías seleccionadas
    public function getArchivosMultimediaNombres(): array { return $this->archivosMultimediaNombres; } // Para los nombres de archivo multimedia


    // Setters
    public function setIdProducto(int $idProducto): void { $this->idProducto = $idProducto; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setDescripcion(?string $descripcion): void { $this->descripcion = $descripcion; }
    public function setTipo(string $tipo): void { $this->tipo = $tipo; }
    public function setPrecio(?float $precio): void { $this->precio = $precio; }
    public function setStock(?int $stock): void { $this->stock = $stock; }
    public function setFechaAlta(string $fechaAlta): void { $this->fechaAlta = $fechaAlta; }
    public function setEstatus(string $estatus): void { $this->estatus = $estatus; }
    public function setIdVendedor(int $idVendedor): void { $this->idVendedor = $idVendedor; }
    public function setIdAdministrador(?int $idAdministrador): void { $this->idAdministrador = $idAdministrador; }
    public function setIdCategorias(array $idCategorias): void { $this->idCategorias = $idCategorias; }
    public function setArchivosMultimediaNombres(array $archivosMultimediaNombres): void { $this->archivosMultimediaNombres = $archivosMultimediaNombres; }

}
?>