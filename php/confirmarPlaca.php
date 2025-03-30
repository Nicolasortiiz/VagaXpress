<?php
require_once("connector.php");

if($_SERVER["REQUEST_METHOD"] === "POST") {
    $placa = $_POST["placa"];
    $placa = preg_replace("/[^A-Z0-9]/i", "", $placa);
    $placa = htmlspecialchars(strip_tags($placa));

    $query = "SELECT * FROM VeiculoEstacionado WHERE placa = ? AND dataSaida IS NULL AND horaSaida IS NULL";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $placa);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stmt-> close();
    $conn-> close();
    if($resultado->num_rows > 0){
        echo json_encode(["resultado" => true]);
    }else{
        echo json_encode(["resultado" => false]);
    }
}
?>