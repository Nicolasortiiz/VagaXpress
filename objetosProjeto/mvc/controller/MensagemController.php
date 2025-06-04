<?php
require_once __DIR__ . "/../dao/MensagemDAO.php";
require_once __DIR__ . "/../model/Mensagem.php";
require_once __DIR__ . "/../controller/UsuarioController.php";

header('Content-Type: application/json');

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

    public function enviar_notificacao($params) {
        $mensagemTexto = $params['notificacao']['placa'] ?? null;

        if (!$mensagemTexto) {
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Nenhuma mensagem recebida.'
            ]);
            return;
        }

        $mensagem = new Mensagem();
        $mensagem->setMensagem($mensagemTexto);

        $resultado = $this->MensagemDAO->inserirNotificacao($mensagem);

        echo json_encode([
            'status' => $resultado ? 'sucesso' : 'erro',
            'mensagem' => $resultado ? 'Notificação inserida com sucesso.' : 'Erro ao inserir notificação.'
        ]);
    }

}