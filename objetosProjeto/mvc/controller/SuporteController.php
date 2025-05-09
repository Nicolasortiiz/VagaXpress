<?php
require_once __DIR__ . "/../dao/SuporteDAO.php";
require_once __DIR__ . "/../model/Suporte.php";

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');


class SuporteController
{
    private $SuporteDAO;

    public function __construct()
    {
        $this->SuporteDAO = new SuporteDAO();
    }
    public function validarLogin()
    {
        if (isset($_SESSION["email"]) && isset($_SESSION["ultima_atividade"]) && isset($_SESSION["usuario_id"])) {
            $ultima_atividade = $_SESSION["ultima_atividade"];
            if (time() - $ultima_atividade > 3600) {
                session_unset();
                session_destroy();
                return false;
            }
            $_SESSION["ultima_atividade"] = time();
            return true;

        } else {
            return false;
        }
    }

    public function enviarMensagemSuporte($mensagem, $email){
        if($this->validarLogin()){
            $email = $_SESSION["email"];
        }
        if($mensagem == null || $email == null){
            json_encode(['error' => true, 'msg' => 'A mensagem e o e-mail nao podem ser vazios!']);
        }

        $suporte = new Suporte();
        $suporte->setEmail($email);
        $suporte->setMensagem($mensagem);

        if($this->SuporteDAO->enviarMensagem($suporte)){
            echo json_encode(['error' => false]);
        }else{
            echo json_encode(['error'=> true,'msg'=> 'Erro no envio da mensagem para o suporte, tente novamente!']);
        }
    }

}

?>