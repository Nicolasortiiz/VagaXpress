<?php
class VagaAgendada {
    private $idVagaAgendada;
    private $placa;
    private $dataEntrada;
    private $horaEntrada;

    public function __construct($idVagaAgendada = null, $placa = null, $dataEntrada = null, $horaEntrada = null) {
        $this->idVagaAgendada = $idVagaAgendada;
        $this->placa = $placa;
        $this->dataEntrada = $dataEntrada;
        $this->horaEntrada = $horaEntrada;
    }

    public function getidVagaAgendada() {
        return $this->idVagaAgendada;
    }

    public function setidVagaAgendada($idVagaAgendada) {
        $this->idVagaAgendada = $idVagaAgendada;
    }
    
    public function getPlaca() {
        return $this->placa;
    }

    public function setPlaca($placa){
        $this->placa = $placa;
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
