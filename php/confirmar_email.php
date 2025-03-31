<?php
require_once "decrypt.php";
require_once "connector.php";

$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);

if(isset($resultado['email'])) {
    $email = $resultado['email'];
}else{
    echo json_encode(['error'=> true]);
}

$querySelect = 'SELECT * FROM Usuario WHERE email = ?';
$stmt = $conn->prepare($querySelect);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$stmt-> close();
$conn-> close();

if($result->num_rows > 0){
    echo json_encode(["error" => true]);
    exit;
}else{
    echo json_encode(["error"=> false]);
}

