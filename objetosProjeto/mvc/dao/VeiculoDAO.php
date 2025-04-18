<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Veiculo.php';

class VeiculoDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function procurarPlaca(Veiculo $veiculo) {
        $querrySelect = "SELECT * FROM Veiculo WHERE placa = ?";
        $placa = $veiculo->getPlaca();
        $stmt = $this->conn->prepare($querrySelect);
        $stmt->bind_param("s", $placa);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if($result->num_rows > 0) {
            return true;
        }else{
            return false;
        }
    }

    public function inserirPlaca(Veiculo $veiculo) {
        $querryInsert = "INSERT INTO Veiculo (placa, idUsuario) VALUES (?, ?)";
        $placa = $veiculo->getPlaca();
        $id = $veiculo->getIdusuario();
        $stmt = $this->conn->prepare($querryInsert);
        $stmt->bind_param("si", $placa,$id);
    
        if($stmt->execute()){
            $stmt->close();
            return true;
        }else{
            $stmt->close();
            return false;
        }
        
    }

    public function deletarPlaca(Veiculo $veiculo) {
        $querryDelete = "DELETE FROM Veiculo WHERE placa = ?";
        $placa = $veiculo->getPlaca();
        $stmt = $this->conn->prepare($querryDelete);
        $stmt->bind_param("s", $placa);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function retornarPlacas(Veiculo $veiculo): array{
        $querySelect = "SELECT placa FROM Veiculo WHERE idUsuario = ?";
        $idUsuario = $veiculo->getIdUsuario();

        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $placas = [];
        while ($row = $result->fetch_assoc()) {
            $placas[] = $row['placa'];
        }
        return $placas;
    }

    public function procurarCadastroPlaca(Veiculo $veiculo){
        $querySelect = 'SELECT placa FROM Veiculo WHERE placa = ? AND idUsuario = ?';
        $idUsuario = $veiculo->getIdUsuario();
        $placa = $veiculo->getPlaca();
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('si', $placa, $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $placaEncontrada = $result->fetch_assoc(); 

        $stmt->close();

        if(count($placaEncontrada) > 0){
            return true;
        }

        return false;
    }

}

?>