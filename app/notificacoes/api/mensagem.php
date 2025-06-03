<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/MensagemController.php";

header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

$controller = new MensagemController();

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataCrypt = file_get_contents('php://input');
    $data = decrypt($dataCrypt);

    switch ($action) {
        case 'enviar_notificacao':
            $controller->enviar_notificacao($data);
            break;
        default:
            http_response_code(400);
            echo json_encode(['erro' => 'Ação POST inválida']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'buscar_notificacoes';
            $controller->retornarNotificacoes();
            break;
        default:
            http_response_code(400);
            echo json_encode(['erro' => 'Erro ao executar a action!']);
    }
} else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método HTTP não permitido']);
}

?>