<?php
require_once("connector.php");

if($_SERVER["REQUEST_METHOD"] === "POST") {
    $placa = $_POST["placa"];
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $valorTotal = $_POST["valorTotal"];

    $placa = htmlspecialchars(strip_tags($placa));
    $nome = htmlspecialchars(strip_tags($nome));
    $cpf = htmlspecialchars(strip_tags($cpf));
    $valorTotal = htmlspecialchars(strip_tags($valorTotal));

    $dataSaida = date('Y-m-d');
    $horaSaida = date('H:i:s');

    $queryVeiculo = "SELECT idVeiculoEstacionado, dataEntrada, horaEntrada FROM VeiculoEstacionado WHERE placa = ? AND dataSaida IS NULL AND horaSaida IS NULL";
    $stmtVeiculo = $conn-> prepare($queryVeiculo);
    $stmtVeiculo-> bind_param("s", $placa);
    $stmtVeiculo-> execute();
    $resultVeiculo = $stmtVeiculo->get_result();
    $veiculo = $resultVeiculo->fetch_assoc();
    $idVeiculo = $veiculo['idVeiculoEstacionado'];
    $stmtVeiculo-> close();

    $updateVeiculoQuery = "UPDATE VeiculoEstacionado SET dataSaida = ?, horaSaida = ? WHERE idVeiculoEstacionado = ?";
    $stmtUpdateVeiculo = $conn->prepare($updateVeiculoQuery);
    $stmtUpdateVeiculo->bind_param("ssi", $dataSaida, $horaSaida, $idVeiculo);
    $stmtUpdateVeiculo->execute();

    if (!$stmtUpdateVeiculo->execute()) {
        echo json_encode(["resultado" => false]);
        exit;
    }
    $stmtUpdateVeiculo -> close();

    $insertNotaFiscalQuery = "INSERT INTO NotaFiscal_Totem (dataEmissao, cpf, nome, valor) VALUES (?, ?, ?, ?)";
    $stmtNotaFiscal = $conn->prepare($insertNotaFiscalQuery);
    $stmtNotaFiscal->bind_param("ssss", $dataSaida, $cpf, $nome, $valorTotal);
    $stmtNotaFiscal-> close();
    $conn-> close();
    if($stmtNotaFiscal->execute()){
        echo json_encode(["resultado" => true]);
    }else{
        echo json_encode(["resultado" => false]);
    }

   
 
}
?>