<?php

class VagaOcupada {
    private $idVaga;
    private $idVeiculoEstacionado;

    public function __construct($idVaga = null, $idVeiculoEstacionado = null) {
        $this->idVaga = $idVaga;
        $this->idVeiculoEstacionado = $idVeiculoEstacionado;
    }

    public function getIdVaga() {
        return $this->idVaga;
    }
    public function setIdVaga($idVaga) {
        $this->idVaga = $idVaga;
    }

    public function getIdVeiculoEstacionado() { 
        return $this->idVeiculoEstacionado;
    }
    
    public function setIdVeiculoEstacionado($idVeiculoEstacionado) {    
        $this->idVeiculoEstacionado = $idVeiculoEstacionado;
    }
}