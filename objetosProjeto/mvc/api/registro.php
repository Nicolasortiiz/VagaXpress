<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/RegistroController.php";


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

$action = $_GET['action'] ?? null;
$controller = new RegistroController();

$placa = $data['placa'] ?? '';
$placa = preg_match('/^[A-Za-z0-9]+$/', $placa) ? $placa : null;

$nome = $data['nome'] ?? '';
$nome = preg_match('/^[\p{L} ]{3,}$/u', trim($nome)) ? trim($nome) : null;

$cpf = $data['cpf'] ?? '';
$cpf = preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf) ? $cpf : null;


switch ($action) {
    case 'pagar_vagas':
        if($nome && $cpf) {
            $controller->pagarVagas($nome, $cpf);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados.']);
            exit;
        }
        break;
    case 'validar_remocao':
        if (!$placa) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->validarExcluir($placa);
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
        exit;  
    }