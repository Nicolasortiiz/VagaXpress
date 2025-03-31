<?php
require_once("decrypt.php");
require_once("connector.php");

$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);

$placa = $resultado["placa"];
$placa = htmlspecialchars(strip_tags($placa));

$query = "SELECT * FROM VeiculoEstacionado WHERE placa = ? AND dataSaida IS NULL AND horaSaida IS NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $placa);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();
$conn->close();

if ($resultado->num_rows > 0) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}

?>