<?php
require_once __DIR__ . "/../controller/ChatOllamaController.php";
require_once __DIR__ . "/../utils/decrypt.php";

header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataCrypt = file_get_contents('php://input');
    $data = decrypt($dataCrypt);
}
$action = $_GET['action'] ?? '';

$controller = new ChatOllamaController();
$mensagem = $data['mensagem'] ?? '';

switch ($action) {
    case 'mensagem_ollama':
        $controller->chamarIA($mensagem);
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
}

?>