<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Registro.php';
require_once __DIR__ . '/../model/Usuario.php';

class RegistroDAO
{
    private $conn;
    public function __construct()
    {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function procurarPlacasDevedoras($placas): array
    {
        $query = 'SELECT placa, dataEntrada, dataSaida, horaEntrada, horaSaida FROM Registro 
              WHERE placa = ? AND satatusPagamento = 0 AND dataSaida IS NOT NULL AND horaSaida IS NOT NULL';

        $devedoras = [];

        foreach ($placas as $placa) {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $placa);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $devedoras[] = [
                    'placa' => $row['placa'],
                    'dataEntrada' => $row['dataEntrada'],
                    'dataSaida' => $row['dataSaida'],
                    'horaEntrada' => $row['horaEntrada'],
                    'horaSaida' => $row['horaSaida']
                ];
            }
            $stmt->close();
        }
        
        return $devedoras;
    }

    public function validarPlaca($placa): bool
    {
        $querySelect = 'SELECT placa FROM Registro WHERE placa = ? AND statusPagamento = 0';
        $stm = $this->conn->prepare($querySelect);
        $stm->bind_param('s', $placa);
        $stm->execute();
        $result = $stm->get_result();
        if($result->num_rows > 0) {
            return false;
        }
        return true;
    }

}

?>