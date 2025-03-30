<?php
require_once "session.php";
require_once "decrypt.php";
require_once __DIR__ . '/otphp/vendor/autoload.php';
use OTPHP\TOTP;

session_start();
$email = $arr['dado1'];

$result = mysqli_query($con, "SELECT nome_usuario FROM usuario WHERE email_usuario LIKE '$email'");
$row = mysqli_fetch_assoc($result);
$otp = TOTP::generate();
mysqli_query($con, "UPDATE usuario SET segredo_usuario = '{$otp->getSecret()}' WHERE email_usuario = '$email'");

$otp->setLabel('Código autenticação');
$qrCode = $otp->getQrCodeUri(
    'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
    '[DATA]'
);

use PHPMailer\PHPMailer\PHPMailer;

require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';

$mail = new PHPMailer();

// Configuração
$mail->Mailer = "smtp";
$mail->IsSMTP();
$mail->CharSet = 'UTF-8';
$mail->SMTPDebug = 0;
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'ssl';
$mail->Host = 'smtp.gmail.com';
$mail->Port = 465;

// Detalhes do envio de E-mail
$mail->Username = 'projectsmirai0';
$mail->Password = "gyzc stjy qumj kgza";
$mail->SetFrom('projectsmirai0@gmail.com', 'projectsmirai0');
$mail->addAddress($email, $row['nome_usuario']);
$mail->Subject = "Confirmação de conta";


// Mensagem
$token = random_int(100000, 999999);
$mensagem = "<div style='font-family: Arial, sans-serif;'>";
$mensagem .= "<h1 style='color: #333333;'>Token: {$token}</h1>";
$mensagem .= "<h1 style='color: #333333;'>Código de autenticação para login:</h1>";
$mensagem .= "<div style='text-align: center;'>";
$mensagem .= "<img src='{$qrCode}'style='margin: 20px auto; display: block;'>";
$mensagem .= "</div>";
$mensagem .= "</div>";



$mail->msgHTML($mensagem);
$mail->send();

$_SESSION['token'] = $token;

mysqli_close($con);
