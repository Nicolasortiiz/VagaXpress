<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/VagaAgendada.php';
require_once __DIR__ .'/../model/Usuario.php';

class VagaAgendadaDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }


}

?>