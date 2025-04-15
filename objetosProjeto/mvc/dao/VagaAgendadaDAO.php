<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/VagaAgendada.php';
require_once __DIR__ .'/../model/Usuario.php';

class VagaAgendadaDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function retornarAgendamentos($id){
        // adicionar lógica para placas
        $query = 'SELECT (idVagaAgendada, placa, horaEntrada, dataEntrada) FROM VagaAgendada WHERE placa = ? ORDER BY dataEntrada DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('', );
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $agendamentos = [];
        while ($row = $result->fetch_assoc()) {
            $agendamentos[] = [
                'id' => $row['idVagaAgendada'],
                'placa' => $row['placa'],
                'data' => $row['dataEntrada'],
                'hora' => $row['horaEntrada']
            ];
        }
        return $agendamentos;
        
    }

    public function criarAgendamento(VagaAgendada $vagaAgendada){
        $query = 'INSERT INTO VagaAgendada (placa, horaEntrada, dataEntrada) VALUES (?, ?, ?)';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sss', $placa, $horaEntrada, $dataEntrada);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }


}

?>