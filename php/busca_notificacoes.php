<?php
header("Content-Type: application/json");

require_once "connector.php";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Aqui vai precisar colocar um "WHERE usuarioID = $id_usuario_sessao para aparecer apenas as mensagens do usuario"
    $stmt = $pdo->query("SELECT idMensagem, mensagem FROM mensagem ORDER BY idMensagem DESC");
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($notificacoes);
} catch (PDOException $e) {
    echo json_encode(["erro" => "Erro na conexão com o banco de dados: " . $e->getMessage()]);
}
