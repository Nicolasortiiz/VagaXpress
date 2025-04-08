<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/NotaFiscal.php';
require_once __DIR__ .'/../model/Usuario.php';

class NotaFiscalDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function retornarInfosNotasFiscais(NotaFiscal $notaFiscal){
        $querySelect = 'SELECT idNotaFiscal, dataEmissao, valor FROM NotaFiscal WHERE idUsuario = ? ORDER BY dataEmissao DESC';
        $idUsuario = $notaFiscal->getIdUsuario();
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $notasFiscais = [];
        while ($row = $result->fetch_assoc()) {
            $notasFiscais[] = [
                'id' => $row['idNotaFiscal'],
                'data' => $row['dataEmissao'],
                'valor' => $row['valor']
            ];
        }
        return $notasFiscais;

    }

}

?>