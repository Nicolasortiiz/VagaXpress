<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/NotaFiscal.php';

class NotaFiscalDAO
{
    private $conn;
    public function __construct()
    {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function retornarInfosNotasFiscais(NotaFiscal $notaFiscal)
    {
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
                'id' => htmlspecialchars($row['idNotaFiscal']),
                'data' => htmlspecialchars($row['dataEmissao']),
                'valor' => htmlspecialchars($row['valor'])
            ];
        }
        return $notasFiscais;

    }

    public function retornarNotaFiscal(NotaFiscal $notaFiscal)
    {
        $querySelect = 'SELECT dataEmissao, cpf, nome, valor, descricao FROM NotaFiscal WHERE idNotaFiscal = ?';
        $idNotaFiscal = $notaFiscal->getIdNotaFiscal();
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('i', $idNotaFiscal);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc(); 
        $stmt->close();
    
        $notaFiscal = [
            'dataEmissao' => htmlspecialchars($row['dataEmissao']),
            'cpf' => htmlspecialchars($row['cpf']),
            'nome' => htmlspecialchars($row['nome']),
            'valor' => htmlspecialchars($row['valor']),
            'descricao' => htmlspecialchars($row['descricao'])
        ];
    
        return $notaFiscal;
    }
    

    public function gerarNotaFiscal(NotaFiscal $notaFiscal){
        $queryInsert = 'INSERT INTO NotaFiscal (idUsuario,dataEmissao,cpf,nome,valor,descricao) VALUES (?,?,?,?,?,?)';
        $idUsuario = $notaFiscal->getIdUsuario();
        $dataEmissao = $notaFiscal->getDataEmissao();
        $cpf = $notaFiscal->getCpf();
        $nome = $notaFiscal->getNome();
        $valor = $notaFiscal->getValor();
        $descricao = $notaFiscal->getDescricao();

        $stmt = $this->conn->prepare($queryInsert);
        $stmt->bind_param('isssds', $idUsuario, $dataEmissao, $cpf, $nome, $valor, $descricao);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

?>