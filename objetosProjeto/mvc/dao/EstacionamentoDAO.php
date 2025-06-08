<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Estacionamento.php';

class EstacionamentoDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function retornarValorHora() {
        $query = 'SELECT valorHora FROM Estacionamento;';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $row = $result->fetch_assoc();
        return $row['valorHora'];
    }

    public function retornarTotalVagas(){
        $query = 'SELECT totalVagas FROM Estacionamento;';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $row = $result->fetch_assoc();
        return $row['totalVagas'];
    }

    public function alteraValorVaga($data){
        $query = 'UPDATE Estacionamento SET valorHora = ?';
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param('d', $data['valor_novo']);

        $success = $stmt->execute();
        $stmt->close();

        return $success ? 1 : 0;
    }
}

?>