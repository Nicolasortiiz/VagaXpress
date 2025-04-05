<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/usuarioController.php";

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
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
}

?>