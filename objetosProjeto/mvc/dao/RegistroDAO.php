<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Registro.php';

class RegistroDAO
{
    private $conn;
    public function __construct()
    {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function procurarPlacasDevedoras($placas): array
    {
        $query = 'SELECT placa, dataEntrada, dataSaida, horaEntrada, horaSaida 
                  FROM Registro 
                  WHERE placa = ? 
                  AND statusPagamento = FALSE
                  AND dataSaida IS NOT NULL 
                  AND horaSaida IS NOT NULL';
    
        $devedoras = [];
    
        foreach ($placas as $placa) {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
            }
    
            $stmt->bind_param('s', $placa);
            $stmt->execute();
            $result = $stmt->get_result();
    
            while ($row = $result->fetch_assoc()) {
                $devedoras[] = [
                    'placa' => htmlspecialchars($row['placa']),
                    'dataEntrada' => htmlspecialchars($row['dataEntrada']),
                    'dataSaida' => htmlspecialchars($row['dataSaida']),
                    'horaEntrada' => htmlspecialchars($row['horaEntrada']),
                    'horaSaida' => htmlspecialchars($row['horaSaida'])
                ];
            }
    
            $stmt->close();
        }
    
        return $devedoras;
    }
    


    public function validarPlaca($placa): bool
    {
        $querySelect = 'SELECT placa FROM Registro WHERE placa = ? AND statusPagamento = 0';
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('s', $placa);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            return false;
        }
        return true;
    }

    public function atualizarStatusPagamento($placas){
        $queryUpdate = 'UPDATE Registro SET statusPagamento = 1 WHERE placa = ?';
        foreach ($placas as $placa) {
            $stmt = $this->conn->prepare($queryUpdate);
            $stmt->bind_param('s', $placa);
            if (!$stmt->execute()) {
                $stmt->close();
                return false;
            }
            $stmt->close();
        }
        return true;
    }


}

?>