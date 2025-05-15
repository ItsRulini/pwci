<?php
// MODELO Chat.php
class Chat {
    private $idChat;
    private $idProducto;

    public function __construct($idChat, $idProducto) {
        $this->idChat = $idChat;
        $this->idProducto = $idProducto;
    }

    public function getIdChat() { return $this->idChat; }
    public function getIdProducto() { return $this->idProducto; }

    public function setIdChat($idChat) { $this->idChat = $idChat; }
    public function setIdProducto($idProducto) { $this->idProducto = $idProducto; }
}
?>