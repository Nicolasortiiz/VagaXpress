<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/NotaFiscal.php';
require_once __DIR__ .'/../model/Usuario.php';

class NotaFiscalDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }


}

?>