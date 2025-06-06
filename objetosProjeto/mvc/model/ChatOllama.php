<?php
class ChatOllama {
    private $idUsuario;

    public function __construct($idUsuario = "") {
        $this->idUsuario = $idUsuario;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($id) {
        $this->idUsuario = $id;
    }
}
?>