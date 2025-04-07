<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Estacionamento.php';

class EstacionamentoDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }


}

?>