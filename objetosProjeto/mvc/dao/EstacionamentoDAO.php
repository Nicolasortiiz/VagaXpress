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
}

?>