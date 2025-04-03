<?php
header('Content-Type: application/json'); 
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (isset($_SESSION["email"]) && isset($_SESSION["ultima_atividade"]) && isset($_SESSION["usuario_id"])) {
    $email = $_SESSION["email"];
    $ultima_atividade = $_SESSION["ultima_atividade"];
    $usuario_id = $_SESSION["usuario_id"];
}else{
    json_encode(["login" => 0, "msg" => "Login necessário"]);
    exit;
}

if (time() - $ultima_atividade > 3600) {
    session_unset(); 
    session_destroy(); 
    echo json_encode(["login" => 0, "msg" => "Sessão expirada"]);
    exit;
}

if ($email == "admin@vagaxpress.com"){
    json_encode(["login" => 2]);
    exit;
} else{
    json_encode(["login"=> 1]);
    exit;
}