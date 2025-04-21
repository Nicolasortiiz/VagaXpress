<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/UsuarioController.php";

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
$nome = $data['nome'] ?? '';
$senha = $data['senha'] ?? '';
$token = $data['token'] ?? '';
$hora = strtotime($data['data']) ?? '';
$saldo = $data['saldo'] ?? 0.0;
$placa = $data['placa'] ?? '';
$id = $data['id'] ?? '';
$valor = $data['valor'] ?? 0.0;

switch ($action) {
    case 'encontrar_email':
        $controller->encontrarEmail($email, $nome);
        break;
    case 'cadastro':
        $controller->cadastro($nome, $email, $senha, $token);
        break;
    case 'validar_conta':
        $controller->validarConta($email, $senha);
        break;
    case 'validar_otp':
        $controller->validarOTP($email, $token, $hora);
        break;
    case 'validar_email':
        $controller->validarEmail($email);
        break;
    case 'validar_token':
        $controller->validarToken($email, $senha, $token);
        break;
    case 'verificar_login_autenticacao':
        $controller->validarLoginAutenticacao();
        break;
    case 'verificar_login_principal':
        $controller->validarLoginPrincipal();
        break;
    case 'adicionar_saldo':
        $controller->adicionarSaldo($saldo);
        break;
    case 'retornar_infos_perfil':
        $controller->retornarInfosPerfil();
        break;
    case 'logout':
        $controller->realizarLogout();
        break;
    case 'validar_pagamento_agendamento':
        $controller->validarPagamentoAgendamento($valor,$placa, $id);
        break;
    case 'realizar_pagamento_agendamento':
        $controller->realizarPagamentoAgendamento($valor, $id);
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
}

?>