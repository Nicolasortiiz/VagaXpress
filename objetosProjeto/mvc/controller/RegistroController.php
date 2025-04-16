<?php
require_once __DIR__ . "/../dao/RegistroDAO.php";
require_once __DIR__ . "/../model/Registro.php";
require_once __DIR__ . "/../controller/EstacionamentoController.php";

header('Content-Type: application/json');
session_start();
date_default_timezone_set('America/Sao_Paulo');


class RegistroController
{
    private $RegistroDAO;
    private $EstacionamentoController;

    public function __construct()
    {
        $this->RegistroDAO = new RegistroDAO();
        $this->EstacionamentoController = new EstacionamentoController();
    }

    public function procurarPlacasDevedoras($placas){
        $devedoras = $this->RegistroDAO->procurarPlacasDevedoras($placas);
        
        if (empty($devedoras)) {
            return json_encode(['error' => false, 'msg' => 'Nenhuma placa devedora encontrada!']);
        }
    
        $valorHora = $this->EstacionamentoController->retornarValorHora();
        $total = 0.0;
    
        foreach ($devedoras as &$devedora) {
            $entrada = strtotime($devedora['horaEntrada']);
            $saida = strtotime($devedora['horaSaida']);
    
            $diferencaHoras = ($saida - $entrada) / 3600; 
            $diferencaHoras = ceil($diferencaHoras); 
    
            $valorEstacionamento = $diferencaHoras * $valorHora;
            $devedora['valor'] = number_format($valorEstacionamento, 2, '.', '');
    
            $total += $valorEstacionamento;
        }
    
        return json_encode([
            'error' => false,
            'total' => number_format($total, 2, '.', ''),
            'devedoras' => $devedoras
        ]);
    }
    
  
}

?>