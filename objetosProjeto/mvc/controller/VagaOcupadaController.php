<?php
require_once __DIR__ . "/../dao/VagaOcupadaDAO.php";
require_once __DIR__ . "/../model/VagaOcupada.php";
require_once __DIR__ . "/../controller/EstacionamentoController.php";

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');

class VagaOcupadaController
{
    private $VagaOcupadaDAO;
    private $EstacionamentoController;

    public function __construct()
    {
        $this->VagaOcupadaDAO = new VagaOcupadaDAO();
        $this->EstacionamentoController = new EstacionamentoController();
    }

    public function retornarQtdVagasLivres(){
        $totalVagas = $this->EstacionamentoController->retornarTotalVagas();
        $vagasOcupadas = intval($this->VagaOcupadaDAO->retornaNumeroVagasOcupadas());
        $vagasLivres = max(0, $totalVagas - $vagasOcupadas);;
        echo json_encode(["vagasLivres" => $vagasLivres]);
    }

   
}

?>