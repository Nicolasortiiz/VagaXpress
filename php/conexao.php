<?php
$host = "localhost";
$usuario = "root";
$senha = "root";
$banco = "EstacionamentoDB";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
