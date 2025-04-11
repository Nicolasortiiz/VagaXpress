<?php
class VagaAgendada {
    private $idVagaAgendada;
    private $idUsuario;
    private $idVeiculo;
    private $dataEntrada;
    private $horaEntrada;

    public function __construct($idVagaAgendada = null, $idUsuario = null, $idVeiculo = null, $dataEntrada = null, $horaEntrada = null) {
        $this->idVagaAgendada = $idVagaAgendada;
        $this->idUsuario = $idUsuario;
        $this->idVeiculo = $idVeiculo;
        $this->dataEntrada = $dataEntrada;
        $this->horaEntrada = $horaEntrada;
    }

    public function getidVagaAgendada$idVagaAgendada() {
        return $this->idVagaAgendada;
    }

    public function setidVagaAgendada$idVagaAgendada($idVagaAgendada) {
        $this->idVagaAgendada = $idVagaAgendada;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    public function getIdVeiculo() {
        return $this->idVeiculo;
    }

    public function setIdVeiculo($idVeiculo) {
        $this->idVeiculo = $idVeiculo;
    }

    public function getDataEntrada() {
        return $this->dataEntrada;
    }

    public function setDataEntrada($dataEntrada) {
        $this->dataEntrada = $dataEntrada;
    }

    public function getHoraEntrada() {
        return $this->horaEntrada;
    }

    public function setHoraEntrada($horaEntrada) {
        $this->horaEntrada = $horaEntrada;
    }
}
?>
