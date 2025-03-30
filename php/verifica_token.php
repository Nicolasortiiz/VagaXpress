<?php
require_once "decrypt.php";
$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);
$input = $resultado['token'];
session_start();
if ($input == $_SESSION['token']) {
    echo json_encode(["status" => 1]);
} else {
    echo json_encode(["status" => 0]);
}

?>
