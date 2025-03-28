<?php
if($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    echo json_encode(["mensagem" => $_POST]);
}
?>