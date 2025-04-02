<?php
session_start();
require_once 'connector.php';
$dados = file_get_contents('php://input');

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["erro" => "Usuário não autenticado."]);
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$valor = $_POST["valor"];
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
    $saldo = $saldo + $valor;
    $stmt = $pdo->prepare("UPDATE Saldo SET saldo = ? WHERE idUsuario = ?");
    $stmt->bind_param("di", $novoSaldo, $usuario_id);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("INSERT INTO Saldo (idUsuario, saldo) VALUES (?, ?)");
    $stmt->bind_param("id", $usuario_id, $valor);
    $stmt->execute();
}

echo json_encode(["sucesso" => number_format($valor, 2, ',', '.')]);



?>