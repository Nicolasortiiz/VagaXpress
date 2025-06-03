<?php
require_once __DIR__ . "/../controller/ChatOllamaController.php";
require_once __DIR__ . "/../utils/decrypt.php";

header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// $dataCrypt = file_get_contents('php://input');
// $data = json_decode($dataCrypt, true);

$data = $_POST;

if (!$data) {
    http_response_code(400);
    echo json_encode(['erro' => 'JSON inválido']);
    exit;
}

$action = $_GET['action'] ?? '';

$controller = new ChatOllamaController();

if (!isset($data['mensagem']) || empty($data['mensagem'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Campo "mensagem" é obrigatório e não pode ser vazio!']);
    exit;
}

$mensagem = $data['mensagem'];

switch ($action) {
    case 'mensagem_ollama':
        try {
            $controller->chamarIA($mensagem);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao chamar IA: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
}
?>
