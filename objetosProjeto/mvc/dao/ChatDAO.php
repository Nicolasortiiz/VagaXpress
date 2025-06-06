<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/ChatOllama.php';

class ChatDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function envia_mensagem($mensagem){
        //asda
    }
}

?>