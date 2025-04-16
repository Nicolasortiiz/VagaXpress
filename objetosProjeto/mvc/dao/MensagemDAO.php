<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Mensagem.php';

class MensagemDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function retornarNotificacoes(){
        $querySelect = "SELECT idMensagem, mensagem FROM Mensagem ORDER BY idMensagem DESC";
        $stmt = $this->conn->prepare($querySelect);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_all();
    }

    public function inserirNotificacao(Mensagem $mensagem) {
        $conn = Conexao::getConexao();
    
        $sql = "INSERT INTO mensagens VALUES (:mensagem)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':mensagem', $mensagem->getMensagem());
    
        return $stmt->execute();
    }    
}

?>