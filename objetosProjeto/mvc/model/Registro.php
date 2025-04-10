<?php
class Registro {
    public $idRegistroEstacionamento;
    public $placa;
    public $dataEntrada;
    public $dataSaida;
    public $horaEntrada;
    public $horaSaida;
    public $statusPagamento;


    public function __construct($idRegistroEstacionamento = null, $placa = null, $dataEntrada = null, 
    $dataSaida = null, $horaEntrada = null, $horaSaida = null, $statusPagamento = null) {
        $this->idRegistroEstacionamento = $idRegistroEstacionamento;
        $this->placa = $placa;
        $this->dataEntrada = $dataEntrada;
        $this->dataSaida = $dataSaida;
        $this->horaEntrada = $horaEntrada;
        $this->horaSaida = $horaSaida;
        $this->statusPagamento = $statusPagamento;
    }

    public function getIdRegistroEstacionamento() {
        return $this->idRegistroEstacionamento;
    }

    public function setIdRegistroEstacionamento($idRegistroEstacionamento) {
        $this->idRegistroEstacionamento = $idRegistroEstacionamento;
    }

    public function getPlaca() {
        return $this->placa;
    }

    public function setPlaca($placa) {
        $this->placa = $placa;
    }

    public function getDataEntrada() {
        return $this->dataEntrada;
    }

    public function setDataEntrada($dataEntrada) {
        $this->dataEntrada = $dataEntrada;
    }

    public function getDataSaida() {   
        return $this->dataSaida;
    }

    public function setDataSaida($dataSaida) {
        $this->dataSaida = $dataSaida;
    }

    public function getHoraEntrada() {
        return $this->horaEntrada;
    }

    public function setHoraEntrada($horaEntrada) {
        $this->horaEntrada = $horaEntrada;
    }

    public function getHoraSaida() {
        return $this->horaSaida;   
    }

    public function setHoraSaida($horaSaida) {
        $this->horaSaida = $horaSaida;
    }

    public function getStatusPagamento() {
        return $this->statusPagamento;
    }

    public function setStatusPagamento($statusPagamento) {
        $this->statusPagamento = $statusPagamento;
    }
}
?>
