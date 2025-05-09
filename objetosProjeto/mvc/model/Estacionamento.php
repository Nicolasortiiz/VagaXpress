<?php

class Estacionamento{
    private $idEstacionamento;
    private $totalVagas;
    private $valorHora;

    public function __construct($idEstacionamento, $totalVagas, $valorHora) {
        $this->idEstacionamento = $idEstacionamento;
        $this->totalVagas = $totalVagas;
        $this->valorHora = $valorHora;
    }

    public function getIdEstacionamento() {
        return $this->idEstacionamento;
    }

    public function setIdEstacionamento($idEstacionamento) {
        $this->idEstacionamento = $idEstacionamento;
    }

    public function getTotalVagas() {
        return $this->totalVagas;
    }

    public function setTotalVagas($totalVagas) {
        $this->totalVagas = $totalVagas;
    }

    public function getValorHora() {
        return $this->valorHora;
    }

    public function setValorHora($valorHora) {    
        $this->valorHora = $valorHora;
    }
}