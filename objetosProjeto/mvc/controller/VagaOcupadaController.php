<?php
require_once __DIR__ . "/../dao/VagaOcupadaDAO.php";
require_once __DIR__ . "/../model/VagaOcupada.php";
require_once __DIR__ . "/../controller/EstacionamentoController.php";

header('Content-Type: application/json');

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
        $vagasOcupadas = $this->VagaOcupadaDAO->retornarTotalVagasOcupadas();
        if($totalVagas == null){
            echo json_encode(["error" => 1, "msg" => "Erro ao buscar dados do estacionamento."]);
            exit;
        }
        $vagasLivres = $totalVagas - $vagasOcupadas;
        if($vagasLivres < 0){
            $vagasLivres = 0;
        }
        echo json_encode(["error" => 0,"vagasLivres" => htmlspecialchars($vagasLivres)]);
        exit;
    }

   
}

?>