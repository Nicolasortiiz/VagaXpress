<?php
require_once __DIR__ . "/../dao/MensagemDAO.php";
require_once __DIR__ . "/../model/Mensagem.php";

session_start();
date_default_timezone_set('America/Sao_Paulo');

class MensagemController{
    private $MensagemDAO;
    
    public function __construct()
    {
        $this->MensagemDAO = new MensagemDAO();
    }

    public function retornarNotificacoes(){
        $notificacoes = $this->MensagemDAO->retornarNotificacoes();
        echo json_encode($notificacoes);
    }

}