<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/VagaAgendadaController.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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
$placa = preg_match('/^[A-Za-z0-9]+$/', $placa) ? $placa : null;

$dataEntrada = $data['data'] ?? '';
$dataEntrada = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataEntrada) ? $dataEntrada : null;

$valor = filter_var($data['valor'] ?? null, FILTER_VALIDATE_FLOAT);
$valor = $valor !== false ? $valor : null;

$nome = $data['nome'] ?? '';
$nome = preg_match('/^[\p{L} ]{3,}$/u', $nome) ? trim($nome) : null;

$cpf = $data['cpf'] ?? '';
$cpf = preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf) ? $cpf : null;

$horaEntrada = $data['hora'] ?? '';
$horaEntrada = preg_match('/^\d{2}:\d{2}$/', $horaEntrada) ? $horaEntrada : null;

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$id = $id !== false ? $id : null;

switch ($action) {
    case 'deletar_agendamentos_placa':
        if($placa){
            $controller->cancelarTodosAgendamentos($placa);
        }else{
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        break;
    case 'procurar_agendamento':
        if($placa && $dataEntrada && $horaEntrada){
            $controller->procurarAgendamento($placa, $dataEntrada, $horaEntrada);
        }else{
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        break;
    case 'criar_agendamento':
        if($placa && $dataEntrada && $horaEntrada && $nome && $cpf){
            $controller->criarAgendamento($placa,$dataEntrada,$horaEntrada,$nome, $cpf);
        }else{
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        break;
    case 'dados_pagina_pagamento':
        $controller->retornarDadosPaginaPagamento();
        break;
    case 'cancelar_agendamento':
        if($id){
            $controller->cancelarAgendamento($id);
        }else{
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
        exit;
}

