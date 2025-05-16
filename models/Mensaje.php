<?php
class Mensaje {
    private $idMensaje;
    private $tipo;
    private $mensaje;
    private $fechaEnvio;
    private $idRemitente;
    private $idChat;

    public function __construct($idMensaje, $tipo, $mensaje, $fechaEnvio, $idRemitente, $idChat) {
        $this->idMensaje = $idMensaje;
        $this->tipo = $tipo;
        $this->mensaje = $mensaje;
        $this->fechaEnvio = $fechaEnvio;
        $this->idRemitente = $idRemitente;
        $this->idChat = $idChat;
    }

    public function getIdMensaje() { return $this->idMensaje; }
    public function getTipo() { return $this->tipo; }
    public function getMensaje() { return $this->mensaje; }
    public function getFechaEnvio() { return $this->fechaEnvio; }
    public function getIdRemitente() { return $this->idRemitente; }
    public function getIdChat() { return $this->idChat; }
}
?>