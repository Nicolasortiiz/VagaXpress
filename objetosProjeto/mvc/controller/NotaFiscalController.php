<?php
require_once __DIR__ . "/../dao/NotaFiscalDAO.php";
require_once __DIR__ . "/../model/NotaFiscal.php";

session_start();
date_default_timezone_set('America/Sao_Paulo');


class RegistroController
{
    private $NotaFiscalDAO;

    public function __construct()
    {
        $this->NotaFiscalDAO = new NotaFiscalDAO();
    }
  
}

?>