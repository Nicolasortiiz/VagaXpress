<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Registro.php';
require_once __DIR__ .'/../model/Usuario.php';

class RegistroDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }


}

?>