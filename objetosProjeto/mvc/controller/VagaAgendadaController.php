<?php
require_once __DIR__ . "/../dao/VagaAgendadaDAO.php";
require_once __DIR__ . "/../model/VagaAgnendada.php";

session_start();
date_default_timezone_set('America/Sao_Paulo');


class VagaAgendadaController
{
    private $VagaAgendadaDAO;

    public function __construct()
    {
        $this->VagaAgendadaDAO = new VagaAgendadaDAO();
    }
  
}

?>