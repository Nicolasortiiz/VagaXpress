<?php
class Veiculo
{
    public $idVeiculo;
    public $idUsuario;
    public $placa;

    public function __construct($idVeiculo = null, $idUsuario = null, $placa = '')
    {
        $this->idVeiculo = $idVeiculo;
        $this->idUsuario = $idUsuario;
        $this->placa = $placa;
    }

    public function getIdVeiculo()
    {
        return $this->idVeiculo;
    }

    public function setIdVeiculo($idVeiculo)
    {
        $this->idVeiculo = $idVeiculo;
    }

    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    public function setIdUsuario($id)
    {
        $this->idUsuario = $id;
    }

    public function getPlaca()
    {
        return $this->placa;
    }

    public function setPlaca($placa)
    {
        $this->placa = $placa;
    }

}
