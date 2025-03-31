<?php
require_once("decrypt.php");
require_once("connector.php");

$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);

$placa = $resultado["placa"];
$placa = htmlspecialchars(strip_tags($placa));

$queryEstacionamento = "SELECT valorHora FROM Estacionamento LIMIT 1";
$result = $conn->query($queryEstacionamento);
$estacionamento = $result->fetch_assoc();
$valorHora = $estacionamento['valorHora'];

$queryVeiculo = "SELECT idVeiculoEstacionado, dataEntrada, horaEntrada FROM VeiculoEstacionado WHERE placa = ? AND dataSaida IS NULL AND horaSaida IS NULL";
$stmtVeiculo = $conn->prepare($queryVeiculo);
$stmtVeiculo->bind_param("s", $placa);
$stmtVeiculo->execute();
$resultVeiculo = $stmtVeiculo->get_result();
$stmtVeiculo->close();

$veiculo = $resultVeiculo->fetch_assoc();
$idVeiculo = $veiculo['idVeiculoEstacionado'];
$dataEntrada = $veiculo['dataEntrada'];
$horaEntrada = $veiculo['horaEntrada'];
$dataSaida = date('Y-m-d');
$horaSaida = date('H:i:s');

$entrada = new DateTime($dataEntrada . ' ' . $horaEntrada);
$saida = new DateTime($dataSaida . ' ' . $horaSaida);
$intervalo = $entrada->diff($saida);

$totalHoras = $intervalo->h + ($intervalo->days * 24);
if ($intervalo->i > 0) {
    $totalHoras += 1;
}

$valorTotal = $totalHoras * $valorHora;
$conn->close();

echo json_encode([
    "valorTotal" => $valorTotal,
    "totalHoras" => $totalHoras
]);


?>