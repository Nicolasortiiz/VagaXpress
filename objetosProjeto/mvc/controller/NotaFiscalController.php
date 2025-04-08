<?php
require_once __DIR__ . "/../dao/NotaFiscalDAO.php";
require_once __DIR__ . "/../model/NotaFiscal.php";
require_once __DIR__ . "/../model/Usuario.php";

session_start();
date_default_timezone_set('America/Sao_Paulo');


class NotaFiscalController
{
    private $NotaFiscalDAO;

    public function __construct()
    {
        $this->NotaFiscalDAO = new NotaFiscalDAO();
    }

    public function retornarInfosNotasFiscaisUsuario(Usuario $usuario){
        $nf = new NotaFiscal();
        $nf->setIdUsuario($usuario->getIdUsuario());
        $notas = [];
        $notas = $this->NotaFiscalDAO->retornarInfosNotasFiscais($nf);
        return $notas;
    }
  
}

?>