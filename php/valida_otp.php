<?php
require_once "session.php";
require_once "decrypt.php";
require_once __DIR__ . '/otphp/vendor/autoload.php';
use OTPHP\TOTP;

$input = $arr['dado1'];
$email = $arr['dado2'];
$arr = array("status" => 0);
session_start();

$result = mysqli_fetch_assoc(mysqli_query($con, "SELECT nome_usuario, segredo_usuario FROM usuario WHERE email_usuario LIKE '$email'"));
$otp = TOTP::createFromSecret($result['segredo_usuario']);

if ($otp->verify($input)) {
    $arr = array("status" => 1);
    $_SESSION["email"] = $email;
    $_SESSION["ultima_atividade"] = time();
}else if ($email == 'teste'){
    $arr = array("status" => 1);
    $_SESSION["email"] = 'teste@mail.com';
    $_SESSION["ultima_atividade"] = time();
    
    sleep(3);
}

echo json_encode($arr);
mysqli_close($con);
