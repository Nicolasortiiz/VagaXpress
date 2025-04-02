<?php
require_once("decrypt.php");
require_once("connector.php");
date_default_timezone_set('America/Sao_Paulo');

$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);

$placa = $resultado["placa"];
$nome = $resultado["nome"];
$cpf = $resultado["cpf"];
$valorTotal = $resultado["valorTotal"];

$placa = htmlspecialchars(strip_tags($placa));
$nome = htmlspecialchars(strip_tags($nome));
$cpf = htmlspecialchars(strip_tags($cpf));
$valorTotal = htmlspecialchars(strip_tags($valorTotal));

$dataSaida = date('Y-m-d');
$horaSaida = date('H:i:s');

$queryVeiculo = "SELECT idVeiculoEstacionado, dataEntrada, horaEntrada FROM VeiculoEstacionado WHERE placa = ? AND dataSaida IS NULL AND horaSaida IS NULL";
$stmtVeiculo = $conn->prepare($queryVeiculo);
$stmtVeiculo->bind_param("s", $placa);
$stmtVeiculo->execute();
$resultVeiculo = $stmtVeiculo->get_result();
$veiculo = $resultVeiculo->fetch_assoc();
$idVeiculo = $veiculo['idVeiculoEstacionado'];
$stmtVeiculo->close();

$updateVeiculoQuery = "UPDATE VeiculoEstacionado SET dataSaida = ?, horaSaida = ? WHERE idVeiculoEstacionado = ?";
$stmtUpdateVeiculo = $conn->prepare($updateVeiculoQuery);
$stmtUpdateVeiculo->bind_param("ssi", $dataSaida, $horaSaida, $idVeiculo);
$stmtUpdateVeiculo->execute();

if (!$stmtUpdateVeiculo->execute()) {
    echo json_encode(["success" => false]);
    exit;
}
$stmtUpdateVeiculo->close();

$insertNotaFiscalQuery = "INSERT INTO NotaFiscal_Totem (dataEmissao, cpf, nome, valor) VALUES (?, ?, ?, ?)";
$stmtNotaFiscal = $conn->prepare($insertNotaFiscalQuery);
$stmtNotaFiscal->bind_param("ssss", $dataSaida, $cpf, $nome, $valorTotal);

if ($stmtNotaFiscal->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
$stmtNotaFiscal->close();
$conn->close();




?>