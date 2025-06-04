<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/UsuarioController.php";

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

$controller = new UsuarioController();
$email = $data['email'] ?? '';
$email = preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email) ? $email : null;

$nome = $data['nome'] ?? '';
$nome = preg_match('/^[\p{L} ]{3,}$/u', $nome) ? trim($nome) : null;

$senha = $data['senha'] ?? null;

$token = $data['token'] ?? '';
$token = preg_match('/^[0-9]{6,}$/', $token) ? $token : null;

$hora = isset($data['data']) ? strtotime($data['data']) : null;

$saldo = $data['saldo'] ?? '';
$saldo = is_numeric($saldo) ? floatval($saldo) : null;

$placa = $data['placa'] ?? '';
$placa = preg_match('/^[A-Za-z0-9]+$/', $placa) ? $placa : null;

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$id = $id !== false ? $id : null;

$valor = filter_var($data['valor'] ?? null, FILTER_VALIDATE_FLOAT);
$valor = $valor !== false ? $valor : null;

$chatId = $data['chatId'] ?? null;


switch ($action) {
    case 'encontrar_email':
        if($email && $nome){
            $controller->encontrarEmail($email, $nome);
        }else{
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        break;
    case 'cadastro':
        if (!$nome || !$email || !$senha || !$token) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->cadastro($nome, $email, $senha, $token);
        break;
    case 'validar_otp':
        if (!$email || !$senha || !$token || !$hora) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->validarOTP($email, $senha,$token, $hora);
        break;
    case 'validar_email':
        if (!$email) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->validarEmail($email);
        break;
    case 'validar_token':
        if (!$email || !$senha || !$token) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->validarToken($email, $senha, $token);
        break;
    case 'verificar_login_autenticacao':
        $controller->validarLoginAutenticacao();
        break;
    case 'verificar_login_principal':
        $controller->validarLoginPrincipal();
        break;
    case 'adicionar_saldo':
        if (!$saldo) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->adicionarSaldo($saldo);
        break;
    case 'retornar_infos_perfil':

        $controller->retornarInfosPerfil();
        break;
    case 'logout':
        $controller->realizarLogout();
        break;
    case 'validar_pagamento_agendamento':
        if (!$valor || !$placa || !$id) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->validarPagamentoAgendamento($valor, $placa, $id);
        break;
    case 'realizar_pagamento':
        if (!$valor || !$id) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->realizarPagamento($valor, $id);
        break;
    case 'verificar_login_suporte':
        $controller->validarLoginSuporte();
        break;
    case 'remover_chat':
        $controller->removerChat();
        break;
    case 'adicionar_chat':
        if (!$chatId) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->adicionarChat($chatId);
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
}

?>