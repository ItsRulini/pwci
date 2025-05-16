<?php
class Oferta {
    private $idOferta;
    private $precio;
    private $estatus;
    private $idMensaje;

    public function __construct($idOferta, $precio, $estatus, $idMensaje) {
        $this->idOferta = $idOferta;
        $this->precio = $precio;
        $this->estatus = $estatus;
        $this->idMensaje = $idMensaje;
    }

    public function getIdOferta() { return $this->idOferta; }
    public function getPrecio() { return $this->precio; }
    public function getEstatus() { return $this->estatus; }
    public function getIdMensaje() { return $this->idMensaje; }
}
?>