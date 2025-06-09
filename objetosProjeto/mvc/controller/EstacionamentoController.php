<?php
require_once __DIR__ . "/../dao/EstacionamentoDAO.php";
require_once __DIR__ . "/../model/Estacionamento.php";

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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

    public function alteraNumeroVagas($data)
    {
        return intval($this->EstacionamentoDAO->alteraNumeroVagas($data));
    }

    public function alteraValorVaga($data)
    {
        return intval($this->EstacionamentoDAO->alteraValorVaga($data));
    }
}

?>