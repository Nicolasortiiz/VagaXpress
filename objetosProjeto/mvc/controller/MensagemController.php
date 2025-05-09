<?php
require_once __DIR__ . "/../dao/MensagemDAO.php";
require_once __DIR__ . "/../model/Mensagem.php";
require_once __DIR__ . "/../controller/UsuarioController.php";

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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