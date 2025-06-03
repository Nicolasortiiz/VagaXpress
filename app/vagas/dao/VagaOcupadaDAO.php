<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/VagaOcupada.php';

class VagaOcupadaDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function retornarTotalVagasOcupadas(): int {
        $query = 'SELECT COUNT(*) as totalVagas FROM VagaOcupada;';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $row = $result->fetch_assoc();
        return intval($row['totalVagas']);
    }
    
    

    
}

?>