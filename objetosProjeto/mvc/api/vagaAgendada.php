<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/VagaAgendadaController.php";

header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataCrypt = file_get_contents('php://input');
    $data = decrypt($dataCrypt);
    
}
$action = $_GET['action'] ?? '';

$controller = new VagaAgendadaController();
$placa = $data['placa'] ?? '';
$dataEntrada = $data['data'] ?? '';
$valor = $data['valor'] ?? 0.0;
$nome = $data['nome'] ?? '';
$cpf = $data['cpf'] ?? '';
$horaEntrada = $data['hora'] ??'';


switch ($action) {
    case 'deletar_agendamentos_placa':
        $controller->cancelarTodosAgendamentos($placa);
        break;
    case 'procurar_agendamento':
        $controller->procurarAgendamento($placa, $dataEntrada, $horaEntrada);
        break;
    case 'criar_agendamento':
        $controller->criarAgendamento($placa,$dataEntrada,$horaEntrada,$nome, $cpf);
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
}

