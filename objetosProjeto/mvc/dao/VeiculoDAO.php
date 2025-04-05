<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Veiculo.php';
require_once __DIR__ .'/../model/Usuario.php';

class VeiculoDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function retornarPlaca(Veiculo $veiculo) {
        $querrySelect = "SELECT * FROM Veiculo WHERE placa = ?";
        $placa = $veiculo->getPlaca();
        $stmt = $this->conn->prepare($querrySelect);
        $stmt->bind_param("s", $placa);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function inserirPlaca(Veiculo $veiculo, Usuario $usuario) {
        $querryInsert = "INSERT INTO Veiculo (placa, idUsuario) VALUES (?, ?)";
        $placa = $veiculo->getPlaca();
        $id = $usuario->getIdusuario();
        $stmt = $this->conn->prepare($querryInsert);
        $stmt->bind_param("si", $placa,$id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
}

?>