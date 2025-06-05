<?php
require_once __DIR__ . "/../dao/EstacionamentoDAO.php";
require_once __DIR__ . "/../model/Estacionamento.php";

header('Content-Type: application/json');

date_default_timezone_set('America/Sao_Paulo');


class EstacionamentoController
{
    private $EstacionamentoDAO;
    

    public function __construct()
    {
        $this->EstacionamentoDAO = new EstacionamentoDAO();
    }

    public function retornarValorHora()
    {
        return floatval($this->EstacionamentoDAO->retornarValorHora());

    }

    public function retornarTotalVagas(): int
    {
        return intval($this->EstacionamentoDAO->retornarTotalVagas());
    }

}

?>