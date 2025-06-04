<?php
class Conexao
{

    private static $instancia = null;
    private $conn;
    private $ip = getenv('DB_HOST');
    private $port = 3309;
    private $username = getenv('DB_USER');
    private $password = getenv('DB_PASS');
    private $dbname = getenv('DB_NAME');

    private function __construct()
    {
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