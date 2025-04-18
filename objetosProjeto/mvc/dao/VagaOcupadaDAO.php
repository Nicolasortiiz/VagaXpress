<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/VagaOcupada.php';

class VagaOcupadaDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function retornaNumeroVagasOcupadas(){
        $querySelect = 'SELECT COUNT(idVagaOcupada) AS qtd FROM VagaOcupada';
        $stmt = $this->conn->prepare($querySelect);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['qtd'];
    }
    
    

    
}

?>