<?php
class Conexao{
    
    private static $instancia = null;
    private $conn;
    private $ip = "127.0.0.1";
    private $username = "root";
    private $password = "root";
    private $dbname = "EstacionamentoDB";

    private function __construct(){
        $this->conn = new mysqli($this->ip, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Erro de conexão: " . $this->conn->connect_error);
        }
    }

    public static function getInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new Conexao();
        }
        return self::$instancia;
    }

    public function getConexao() {
        return $this->conn;
    }
}
?>