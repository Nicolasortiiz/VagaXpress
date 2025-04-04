<?php
header('Content-Type: application/json'); 
session_start();
require_once "connector.php";

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["erro" => "Usuário não autenticado."]);
    exit;
}

$usuario_id = intval($_SESSION["usuario_id"]);

$querySelect = "SELECT idNotaFiscal, dataEmissao FROM NotaFiscal_Usuario WHERE idUsuario = ?";
$stmt = $conn->prepare($querySelect);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$idNotaFiscal = $result->fetch_assoc()['idNotaFiscal'];
$dataEmissao = $result->fetch_assoc()['dataEmissao'];

if($idNotaFiscal){
    echo json_encode(["idNotaFiscal" => $idNotaFiscal,"dataEmissao" => $dataEmissao]);
}else{
    echo json_encode(["idNotaFiscal" => null]);
}


?>