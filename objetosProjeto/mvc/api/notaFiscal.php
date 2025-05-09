<?php
require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/NotaFiscalController.php";

header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataCrypt = file_get_contents('php://input');
    $data = decrypt($dataCrypt);
}
$action = $_GET['action'] ?? '';

$controller = new NotaFiscalController();
$idNotaFiscal = $data['idNotaFiscal'] ?? '';
$valor = $data['valor'] ?? 0.0;
$id = $data['id'] ?? 0.0;
$nome = $data['nome'] ?? 0.0;
$cpf = $data['cpf'] ?? 0.0;
$descricao = $data['descricao'] ?? 0.0;

switch ($action) {
    case 'retornar_notas_fiscais':
        $controller->retornarInfosNotasFiscaisUsuario();
        break;
    case 'retornar_detalhes_nf':
        $controller->retornarDetalhesNotaFiscal($idNotaFiscal);
        break;
    case 'gerar_nota_fiscal':
        $controller->gerarNotaFiscal($id, $cpf, $nome, $valor, $descricao);
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
}

?>