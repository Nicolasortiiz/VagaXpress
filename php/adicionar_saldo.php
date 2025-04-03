<?php
header('Content-Type: application/json'); 
session_start();
require_once 'connector.php';
require_once 'decrypt.php';

$dados = file_get_contents('php://input');
$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["erro" => "Usuário não autenticado."]);
    exit;
}

$usuario_id = intval($_SESSION["usuario_id"]);
$valor = $resultado["valor"];
$valor = str_replace(["R$", " "], "", $valor);
$valor = floatval(str_replace(",", ".", $valor));

if ($valor <= 0) {
    echo json_encode(["erro" => "O valor precisa ser maior que zero."]);
    exit;
}

$querySelect ="SELECT saldo FROM Saldo WHERE idUsuario = ?";

$stmt = $conn ->prepare($querySelect);
$stmt->bind_param("i",$usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$saldo = $result->fetch_assoc()['saldo'] ?? false;

if ($saldo !== false) {
    $queryUpdate = "UPDATE Saldo SET saldo = ? WHERE idUsuario = ?";
    $saldo = $saldo + $valor;
    $stmt = $conn->prepare($queryUpdate);
    $stmt->bind_param("di", $saldo, $usuario_id);
    $stmt->execute();
} else {
    $queryInsert = "INSERT INTO Saldo (idUsuario, saldo) VALUES (?, ?)";
    $stmt = $conn->prepare($queryInsert);
    $stmt->bind_param("id", $usuario_id, $valor);
    $stmt->execute();
}

echo json_encode(["sucesso" => number_format($valor, 2, ',', '.')]);



?>