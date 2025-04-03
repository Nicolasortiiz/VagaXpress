<?php
header('Content-Type: application/json'); 
session_start();
require_once "connector.php";

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["erro" => "Usuário não autenticado."]);
    exit;
}

$usuario_id = intval($_SESSION["usuario_id"]);

$querySelect = "SELECT saldo FROM Saldo WHERE idUsuario = ?";
$stmt = $conn->prepare($querySelect);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$saldo = $result->fetch_assoc()['saldo'] ?? 0.00;

echo json_encode(["saldo" => $saldo]);

?>