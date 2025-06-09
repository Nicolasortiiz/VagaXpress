<?php
class Conexao
{

    private static $instancia = null;
    private $conn;
    private $ip;
    private $port;
    private $username;
    private $password;
    private $dbname;

    private function __construct()
    {
        $this->ip = 'db-estacionamento-service';
        $this->port = '3306';
        $this->username = getenv('DB_USER');
        $this->password = getenv('DB_PASS');
        $this->dbname = getenv('DB_NAME');
        $this->conn = new mysqli(
            $this->ip,
            $this->username,
            $this->password,
            $this->dbname,
            $this->port
        );

        if ($this->conn->connect_error) {
            die("Erro de conexão: " . $this->conn->connect_error);
        }
    }

    public static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new Conexao();
        }
        return self::$instancia;
    }

    public function getConexao()
    {
        return $this->conn;
    }
}
?>