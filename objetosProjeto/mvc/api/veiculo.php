<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/VeiculoController.php";

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
$id = $data['id'] ?? null;


switch ($action) {
    case 'cadastrar_placa':
        $controller->cadastrarPlaca($placa);
        break;
    case 'retornar_placas':
        $controller->retornarPlacas($id);
        break;
    case 'deletar_placa':
        $controller->deletarPlaca($placa, $id);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => true, 'msg' => 'Ação inválida ou não reconhecida.']);
        exit;
}

?>