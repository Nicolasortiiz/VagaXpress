<?php
require_once "decrypt.php";
require_once "connector.php";
require_once __DIR__ . '/otphp/vendor/autoload.php';
use OTPHP\TOTP;

$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);
date_default_timezone_set('America/Sao_Paulo');

$input = $resultado['input'] ?? null;
$email = $resultado['email'];
session_start();

// tirar usuário teste
if ($email == 'teste'){
    echo json_encode(["success" => true]);
    $_SESSION["email"] = "teste@mail.com";
    $_SESSION["ultima_atividade"] = time();
    $conn->close();
    exit;
}

$query = "SELECT nome, segredo FROM Usuario WHERE email LIKE ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();
$conn->close();
$otp = TOTP::createFromSecret($usuario['segredo']);

$horaComAjuste = time() + 40;

if ($otp->at($horaComAjuste) == $input) {
    $status = ["success" => true];
    $_SESSION["email"] = $email;
    $_SESSION["ultima_atividade"] = time();
}else{
    $status = ["success"=> false];
}

echo json_encode($status);

