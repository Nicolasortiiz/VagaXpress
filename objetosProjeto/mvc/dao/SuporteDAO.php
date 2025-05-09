<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Suporte.php';

class SuporteDAO
{
    private $conn;
    public function __construct()
    {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function enviarMensagem(Suporte $suporte)
    {
        $queryInsert = 'INSERT INTO Suporte (email, mensagem) VALUES (?, ?)';
        $email = $suporte->getEmail();
        $msg = $suporte->getMensagem();

        $stmt = $this->conn->prepare($queryInsert);
        $stmt->bind_param('ss', $email, $msg);
        $result = $stmt->execute();
        $stmt->close();
        return $result;

    }

}

?>