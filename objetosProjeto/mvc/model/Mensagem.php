<?php
class Mensagem
{
    private $idMensagem;
    private $mensagem;

    public function __construct($idMensagem = null, $mensagem = '')
    {
        $this->idMensagem = $idMensagem;
        $this->mensagem = $mensagem;
    }
    public function getIdMensagem()
    {
        return $this->idMensagem;
    }
    public function setIdMensagem($idMensagem)
    {
        $this->idMensagem = $idMensagem;
    }
    public function getMensagem()
    {
        return $this->mensagem;
    }
    public function setMensagem($mensagem)
    {
        $this->mensagem = $mensagem;
    }
}
?>