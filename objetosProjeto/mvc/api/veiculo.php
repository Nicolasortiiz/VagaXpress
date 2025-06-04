<?php
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://redis-service:6379');

require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/VeiculoController.php";

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

$controller = new VeiculoController();
$placa = $data['placa'] ?? '';
$placa = preg_match('/^[A-Za-z0-9]+$/', $placa) ? $placa : null;

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$id = $id !== false ? $id : null;


switch ($action) {
    case 'cadastrar_placa':
        if (!$placa) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->cadastrarPlaca($placa);
        break;
    case 'retornar_placas':
        $controller->retornarPlacas($id);
        break;
    case 'deletar_placa':
        if (!$placa || !$id) {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados!']);
            exit;
        }
        $controller->deletarPlaca($placa, $id);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => true, 'msg' => 'Ação inválida ou não reconhecida.']);
        exit;
}

?>