<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/SuporteController.php";

header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataCrypt = file_get_contents('php://input');
    $data = decrypt($dataCrypt);
    
}
$action = $_GET['action'] ?? '';    

$controller = new SuporteController();
$email = $data['email'] ?? '';
$tipoMsg = $data['tipo'] ?? '';
$texto = $data['texto'] ?? '';
$token = $data['token'] ?? '';

switch ($action) {
    case 'confirmar_email':
        $controller->confirmarEmail($email);
        break;
    case 'enviar_suporte_deslogado':
        $controller->enviarSuporteDeslogado($email,$texto, $token);
        break;
    case 'enviar_suporte_logado':
        $controller->enviarSuporteLogado($texto,$tipoMsg);
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
        exit;
}

