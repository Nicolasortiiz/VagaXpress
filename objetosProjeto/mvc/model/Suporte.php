<?php

class Suporte
{
    private $idSuporte;
    private $email;
    private $mensagem;
    private $tipo;

    public function __construct($idSuporte = null, $email = '', $mensagem = '')
    {
        $this->idSuporte = $idSuporte;
        $this->email = $email;
        $this->mensagem = $mensagem;
    }

    public function setIdSuporte($idSuporte)
    {
        $this->idSuporte = $idSuporte;
    }

    public function getIdSuporte()
    {
        return $this->idSuporte;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setMensagem($mensagem)
    {
        $this->mensagem = $mensagem;
    }

    public function getMensagem()
    {
        return $this->mensagem;
    }

    public function getTipo(){
        return $this->tipo;
    }
    public function setTipo($tipo){
        $this->tipo = $tipo;
    }
    
}