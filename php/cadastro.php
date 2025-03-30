<?php
/*

ADICIONAR cognito

*/
session_start();
require_once "decrypt.php";
require_once "connector.php";
require_once __DIR__ . '/otphp/vendor/autoload.php';
use OTPHP\TOTP;

if(!isset($_SESSION['qr'])) {
    json_encode(['error'=> true]);
    exit;
}

$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);
$segredo = $_SESSION['qr'];

if(isset($resultado['email']) && isset($resultado['nome']) && isset($resultado['senha'])) {
    $email = $resultado['email'];
    $username = $resultado['nome'];
    $senha = $resultado['senha'];
}else{
    echo json_encode(array('error'=> true));
}
$querySelect = 'SELECT * FROM Usuario WHERE email = ?';
$stmt = $conn->prepare($querySelect);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $status = ["error" => 1];
    echo json_encode($status);
    exit;
}

$queryInsert = "INSERT INTO Usuario (nome, email, senha, segredo) VALUES (?, ?, ?, ?)";
$stmt = $conn-> prepare($queryInsert);
$stmt-> bind_param("ssss", $username,$email, $senha, $segredo);

if(!$stmt-> execute()){
    $status = ["error"=> 2];
}

$stmt-> close();
$conn-> close();
unset($_SESSION['qr']);
$status = ["success" => 1];

echo json_encode($status);

?>