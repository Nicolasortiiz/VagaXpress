<?php
require_once __DIR__ . "/../dao/RegistroDAO.php";
require_once __DIR__ . "/../model/Registro.php";

session_start();
date_default_timezone_set('America/Sao_Paulo');


class RegistroController
{
    private $RegistroDAO;

    public function __construct()
    {
        $this->RegistroDAO = new RegistroDAO();
    }
  
}

?>