<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../model/Usuario.php';

class UsuarioDAO {
    private $conn;
    public function __construct() {
        $this->conn = Conexao::getInstancia()->getConexao();
    }

    public function cadastrar(Usuario $usuario, $senha){
        $queryInsert = "INSERT INTO Usuario (nome, email, senha, segredo, saldo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($queryInsert);
        $nome = $usuario->getNome();
        $email = $usuario->getEmail();
        $segredo = $usuario->getSegredo();
        $saldo = $usuario->getSaldo();
        $stmt-> bind_param("ssssd", $nome,$email, $senha, $segredo, $saldo);
        $result = $stmt-> execute();
        $stmt->close();
        if(!$result){
            return false;
        }else{
            return true;
        }    
    }

    public function encontrarEmail(Usuario $usuario): bool {
        $email = $usuario->getEmail();
        $querySelect = 'SELECT * FROM Usuario WHERE email = ?';
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt-> close();

        if($result->num_rows > 0){
            return true;
        }else{
            return false;
        }
    }

    public function validarConta(Usuario $usuario,$senha): bool {
        $email = $usuario->getEmail();
        $query = "SELECT * FROM Usuario WHERE email = ? AND senha = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $email, $senha);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();
        if($resultado->num_rows > 0){
            return true;
        }else{  
            return false;
        }
    }

    public function retornaIdUsuario(Usuario $usuario): int {
        $email = $usuario->getEmail();
        $query = "SELECT idUsuario FROM Usuario WHERE email LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $id = $resultado->fetch_assoc()['idUsuario'];
        $stmt->close();
        if($id){
            return $id;
        }else{
            return 0;
        }
    }
    
    public function retornaSegredoUsuario(Usuario $usuario){
        $email = $usuario->getEmail();
        $query = "SELECT segredo FROM Usuario WHERE email LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $segredo = $resultado->fetch_assoc()['segredo'];
        $stmt->close();

        if($segredo){
            return $segredo;
        }else{
            return false;
        }
    }

    public function encontrarNome(Usuario $usuario) {
        $querySelect = "SELECT nome FROM Usuario WHERE email LIKE ?";
        $stmt = $this->conn->prepare($querySelect);
        $email = $usuario->getEmail();
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if($result){
            return $result->fetch_assoc()['nome'];
        }else{
            return false;
        }
    }

    public function updateUsuario(Usuario $usuario, $senha, $segredo): bool {
        $queryUpdate = 'UPDATE Usuario SET senha = ?, segredo = ? WHERE email = ?';
        $stmt = $this->conn->prepare($queryUpdate);
        $email = $usuario->getEmail();
        $stmt->bind_param('sss', $senha, $segredo, $email);
        $result = $stmt->execute();
        $stmt->close();
    
        return $result;
    }

    public function adicionarSaldo(Usuario $usuario){
        $querySelect = "SELECT saldo FROM Usuario WHERE idUsuario = ?";
        $queryUpdate = "UPDATE Usuario SET saldo = ? WHERE idUsuario = ?";
        
        $idUsuario = $usuario->getIdUsuario();
        $novoSaldo = $usuario->getSaldo();
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();  
        $result = $stmt->get_result();
    
        if ($result && $result->num_rows > 0) {
            $saldo = $result->fetch_assoc()["saldo"];
            $novoSaldo = $saldo + $novoSaldo;
            $stmt->close();
            $stmt = $this->conn->prepare($queryUpdate);
            $stmt->bind_param("di", $novoSaldo, $idUsuario);
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            } else {
                $stmt->close();
                return false;
            }
    
        } else {
            $stmt->close();
            return false;
        }
    }
    

    public function retornarSaldo(Usuario $usuario){
        $querySelect = "SELECT saldo FROM Usuario WHERE idUsuario = ?";
        $idUsuario = $usuario->getIdUsuario();

        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $saldo = $result->fetch_assoc()['saldo'] ?? false;

        return $saldo;
    }

    public function retornarNotificacoes(){
        $querySelect = "SELECT idMensagem, mensagem FROM Mensagem ORDER BY idMensagem DESC";
        $stmt = $this->conn->prepare($querySelect);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_all();
    }

    public function retornarNome(Usuario $usuario){
        $querySelect = 'SELECT nome FROM Usuario WHERE idUsuario = ?';
        $idUsuario = $usuario->getIdUsuario();
        $stmt = $this->conn->prepare($querySelect);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc()['nome'];
    }   


    public function atualizarSaldo(Usuario $usuario)
    {
        $queryUpdate = 'UPDATE Usuario SET saldo=? WHERE idUsuario=?';
        $id = $usuario->getIdUsuario();
        $saldo = $usuario->getSaldo();
        $stmt = $this->conn->prepare($queryUpdate);
        $stmt->bind_param('di', $saldo, $id);
        $result = $stmt->execute();
        $stmt->close();
    
        return $result;
    }
}

?>