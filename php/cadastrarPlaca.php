<?php
header("Content-Type: application/json");

require_once "connector.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["placa"]) || !isset($data["usuario_id"])) {
    echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos"]);
    exit;
}

$placa = $data["placa"];
$usuario_id = intval($data["usuario_id"]);

if ($conn->connect_error) {
    echo json_encode(["status" => "erro", "mensagem" => "Erro na conexão: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO Veiculo (placa, idUsuario) VALUES (?, ?)");

if (!$stmt) {
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao preparar a consulta: " . $conn->error]);
    exit;
}

$stmt->bind_param("si", $placa, $usuario_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "sucesso", "mensagem" => "Veículo cadastrado com sucesso"]);
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar veículo: " . $stmt->error]);
}

$stmt->close();
$conn->close();
