<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/VagaAgendada.php';
require_once __DIR__ . '/../model/Usuario.php';

class VagaAgendadaDAO
{
    private $conn;
    public function __construct()
    {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function retornarAgendamentos($placas): array
    {
        $query = 'SELECT idVagaAgendada, placa, horaEntrada, dataEntrada FROM VagaAgendada WHERE placa = ? ORDER BY dataEntrada DESC';
        $agendamentos = [];

        foreach ($placas as $placa) {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $placa);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $agendamentos[] = [
                    'id' => $row['idVagaAgendada'],
                    'placa' => $row['placa'],
                    'data' => $row['dataEntrada'],
                    'hora' => $row['horaEntrada']
                ];
            }
            $stmt->close();
        }
        return $agendamentos;

    }

    public function criarAgendamento(VagaAgendada $vagaAgendada)
    {
        $query = 'INSERT INTO VagaAgendada (placa, horaEntrada, dataEntrada) VALUES (?, ?, ?)';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sss', $placa, $horaEntrada, $dataEntrada);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function retornarHoraChegada(VagaAgendada $agendamento){
        $querySelect = 'SELECT horaChegada FROM VagaAgendada WHERE idVagaAgendada = ?';
        $id = $agendamento->getidVagaAgendada();
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $row = $result->fetch_assoc();
        return $row['horaChegada'];
    }

    public function retornarDataChegada(VagaAgendada $agendamento){
        $querySelect = 'SELECT dataChegada FROM VagaAgendada WHERE idVagaAgendada = ?';
        $id = $agendamento->getidVagaAgendada();
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $row = $result->fetch_assoc();
        return $row['dataChegada'];
    }

    public function removerAgendamento(VagaAgendada $agendamento){
        $queryDelete = 'DELETE FROM VagaAgendada WHERE idVagaAgendada = ?';
        $id = $agendamento->getidVagaAgendada();
        $stmt = $this->conn->prepare($queryDelete);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    
        $linhasAfetadas = $stmt->affected_rows;
        $stmt->close();
        
        if($linhasAfetadas > 0){
            return true;
        }
        return false;
    }
    
}

?>