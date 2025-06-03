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
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function inserirNotificacao(Mensagem $mensagem) {
        $sql = "INSERT INTO Mensagem (mensagem) VALUES (?)";
        $stmt = $this->conn->prepare($sql);

        $mensagemTexto = $mensagem->getMensagem();
        $stmt->bind_param("s", $mensagemTexto);

        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

}

?>