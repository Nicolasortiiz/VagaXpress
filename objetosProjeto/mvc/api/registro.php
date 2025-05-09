<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/RegistroController.php";

header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataCrypt = file_get_contents('php://input');
    $data = decrypt($dataCrypt);
}

$action = $_GET['action'] ?? '';
$controller = new RegistroController();
$placas = $data['placas'] ?? '';
$placa = $data['placa'] ?? '';
$nome = $data['nome'] ?? '';
$cpf = $data['cpf'] ?? '';


switch ($action) {
    case 'retornar_vagas_devedoras':
        $controller->procurarPlacasDevedoras($placas);
        break;
    case 'pagar_vagas':
        $controller->pagarVagas( $nome, $cpf);
        break;
    case 'validar_remocao':
        $controller->validarExcluir($placa);
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
        exit;  
    }