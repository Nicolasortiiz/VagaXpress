<?php
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://redis-service:6379');

require_once __DIR__ . "/../utils/decrypt.php";
require_once __DIR__ . "/../controller/NotaFiscalController.php";

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

$controller = new NotaFiscalController();

$idNotaFiscal = filter_var($data['idNotaFiscal'] ?? null, FILTER_VALIDATE_INT);
$idNotaFiscal = $idNotaFiscal !== false ? $idNotaFiscal : null;

$valor = filter_var($data['valor'] ?? null, FILTER_VALIDATE_FLOAT);
$valor = $valor !== false ? $valor : null;

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$id = $id !== false ? $id : null;

$nome = $data['nome'] ?? '';
$nome = preg_match('/^[\p{L} ]{3,}$/u', $nome) ? trim($nome) : null;

$cpf = $data['cpf'] ?? '';
$cpf = preg_match('/^\d{11}$/', $cpf) ? $cpf : null;

$descricao = substr(trim($data['descricao'] ?? ''), 0, 255);

switch ($action) {
    case 'retornar_notas_fiscais':
        $controller->retornarInfosNotasFiscaisUsuario();
        break;
    case 'retornar_detalhes_nf':
        if($idNotaFiscal){
            $controller->retornarDetalhesNotaFiscal($idNotaFiscal);
        }else{
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados.']);
            exit;
        }
        
        break;
    case 'gerar_nota_fiscal':
        if ($id  && $cpf && $nome && $valor) {
            $controller->gerarNotaFiscal($id, $cpf, $nome, $valor, $descricao);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Erro no processamento dos dados.']);
            exit;
        }
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Erro ao executar a action!']);
}

?>