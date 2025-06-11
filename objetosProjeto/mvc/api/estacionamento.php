<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/EstacionamentoController.php";

header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

$controller = new EstacionamentoController();

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataCrypt = file_get_contents('php://input');
    $data = decrypt($dataCrypt);

    switch ($action) {
        case 'altera_numero_vagas':
            $resultado = $controller->alteraNumeroVagas($data);

            header('Content-Type: application/json');

            echo json_encode([
                "status" => $resultado ? "sucesso" : "erro",
                "codigo" => $resultado
            ]);
        break;

        case 'altera_valor_vaga':
            $resultado = $controller->alteraValorVaga($data);

            header('Content-Type: application/json');

            echo json_encode([
                "status" => $resultado ? "sucesso" : "erro",
                "codigo" => $resultado
            ]);
        break;

        default:
            http_response_code(400);
            echo json_encode(['erro' => 'Ação POST inválida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método HTTP não permitido']);
}

?>