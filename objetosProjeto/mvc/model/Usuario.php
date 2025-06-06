<?php
class Usuario {
    private $idUsuario;
    private $nome;
    private $email;
    private $saldo;
    private $segredo;
    private $chatId;

    public function __construct($idUsuario = null, $nome = '', $email = '', $saldo = 0.0, $segredo = '', $chatId = '') {
        $this->idUsuario = $idUsuario;
        $this->nome = $nome;
        $this->email = $email;
        $this->saldo = $saldo;
        $this->segredo = $segredo;
        $this->chatId = $chatId;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($id) {
        $this->idUsuario = $id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {   
        $this->nome = $nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getSaldo() {
        return $this->saldo;
    }

    public function setSaldo($saldo) {
        $this->saldo = $saldo;
    }

    public function getSegredo() {
        return $this->segredo;
    }

    public function setSegredo($segredo) {
        $this->segredo = $segredo;
    }

    public function getChatId() {
        return $this->chatId;
    }
    
    public function setChatId($chatId) {
        $this->chatId = $chatId;
    }
}
?>