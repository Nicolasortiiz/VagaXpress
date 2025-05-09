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
        $placa = $vagaAgendada->getPlaca();
        $horaEntrada = $vagaAgendada->getHoraEntrada();
        $dataEntrada = $vagaAgendada->getDataEntrada();
        $stmt->bind_param('sss', $placa, $horaEntrada, $dataEntrada);
        $stmt->execute();
        $result = $stmt->affected_rows > 0;
        $stmt->close();
        return $result;
    }

    public function retornarHoraChegada(VagaAgendada $agendamento){
        $querySelect = 'SELECT horaEntrada FROM VagaAgendada WHERE idVagaAgendada = ?';
        $id = $agendamento->getidVagaAgendada();
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($row = $result->fetch_assoc()) {
            return $row['horaEntrada'];
        }
        return null;
    }

    public function retornarDataChegada(VagaAgendada $agendamento){
        $querySelect = 'SELECT dataEntrada FROM VagaAgendada WHERE idVagaAgendada = ?';
        $id = $agendamento->getidVagaAgendada();
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($row = $result->fetch_assoc()) {
            return $row['dataEntrada']; 
        }
        return null;
    }

    public function removerAgendamento(VagaAgendada $agendamento): bool{
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

    public function removerTodosAgendamentos(VagaAgendada $agendamento){
        $queryDelete = 'DELETE FROM VagaAgendada WHERE placa = ?';
        $placa = $agendamento->getPlaca();
        $stmt = $this->conn->prepare($queryDelete);
        $stmt->bind_param('s', $placa);
        $stmt->execute();
        $stmt->close();
    }

    public function procurarAgendamento(VagaAgendada $agendamento):bool{
        $querySelect = 'SELECT * FROM VagaAgendada WHERE placa = ? AND DATE(dataEntrada) = ? AND TIME(horaEntrada) = ?';
        
        $placa = $agendamento->getPlaca();
        $dataEntrada = $agendamento->getDataEntrada();
        $horaEntrada = $agendamento->getHoraEntrada();
    
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('sss', $placa, $dataEntrada, $horaEntrada);
        $stmt->execute();
    
        $result = $stmt->get_result(); 
        $stmt->close();
        
        if ($result ->num_rows > 0) {
            return true;
        }
        
        return false;
    }
    
}

?>