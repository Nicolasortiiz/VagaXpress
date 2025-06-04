<?php
class Conexao{
    
    private static $instancia = null;
    private $conn;
    private $ip;
    private $username;
    private $password;
    private $dbname;

    private function __construct(){
        $env = parse_ini_file(__DIR__ . '/../.env');
        $this->ip = $env['DATABASE_IP'] ?? $this->ip;
        $this->username = $env['DATABASE_USER'] ?? $this->username;
        $this->password = $env['DATABASE_PASSWORD'] ?? $this->password;
        $this->dbname = "EstacionamentoDB" ?? $this->dbname;

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