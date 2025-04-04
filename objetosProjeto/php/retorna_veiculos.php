<?php
header('Content-Type: application/json'); 
session_start();
require_once "connector.php";

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["erro" => "Usuário não autenticado."]);
    exit;
}

$usuario_id = intval($_SESSION["usuario_id"]);

$querySelect = "SELECT placa FROM Veiculo WHERE idUsuario = ?";
$stmt = $conn->prepare($querySelect);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$placa = $result->fetch_assoc()['placa'];
if($placa){
    echo json_encode(["placas" => $placa]);
}else{
    echo json_encode(["placas" => null]);
}


?>