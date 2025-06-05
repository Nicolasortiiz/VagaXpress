<?php
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://redis-service:6379');

require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/SuporteController.php";

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

$controller = new SuporteController();
$email = $data['email'] ?? '';
$email = preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email) ? $email : null;

$tipoMsg = $data['tipo'] ?? '';
$tipoMsg = in_array($tipoMsg, ['Problema', 'Duvida', 'Colaborador', 'Outro']) ? $tipoMsg : null;

$texto = $data['texto'] ?? '';
$texto = substr(trim($texto), 0, 500) ? $texto : null;

$token = $data['token'] ?? '';
$token = preg_match('/^[0-9]{6,}$/', $token) ? $token : null;

switch ($action) {
    case 'validar_email':
        if($email){
            $controller->confirmarEmail($email);
        }else{
            echo json_encode(['error' => true, 'msg' => 'Erro no proessamento dos dados!']);
            exit;
        }
        
        break;
    case 'enviar_suporte_deslogado':
        if($email && $texto && $token && $tipoMsg){
            $controller->enviarSuporteDeslogado($email,$texto, $token, $tipoMsg);
        }else{
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        break;
    case 'enviar_suporte_logado':
        if($texto && $tipoMsg){
            $controller->enviarSuporteLogado($texto,$tipoMsg);
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

