<?php
require_once __DIR__ . "/../dao/SuporteDAO.php";
require_once __DIR__ . "/../model/Suporte.php";

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');
use PHPMailer\PHPMailer\PHPMailer;

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

    public function enviarSuporteDeslogado($email, $texto, $token){
        if($texto == null || $email == null){
            json_encode(['error' => true, 'msg' => 'A mensagem e o e-mail nao podem ser vazios!']);
            exit;
        }
        if($token != $_SESSION['token'] || $_SESSION['token'] == null){
            json_encode(['error' => true, 'msg' => 'Token invalido!']);
            exit;
        }else{
            $suporte = new Suporte();
            $suporte->setEmail($email);
            $suporte->setMensagem($texto);
            $suporte->setTipo("Deslogado");

            if($this->SuporteDAO->enviarMensagem($suporte)){
                echo json_encode(['error' => false]);
                exit;
            }else{
                echo json_encode(['error'=> true,'msg'=> 'Erro no envio da mensagem para o suporte, tente novamente!']);
                exit;
            }

        }

        
    }

    public function confirmarEmail($email){
        if($email == null){
            json_encode(['error' => true, 'msg' => 'O e-mail nao pode ser vazio!']);
            exit;
        }

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->Username = 'projectsmirai0@gmail.com';
        $mail->Password = 'gyzc stjy qumj kgza';
        $mail->setFrom('projectsmirai0@gmail.com', 'VagaXpress');
        $mail->addAddress($email, "Usuário");
        $mail->Subject = "Confirmação de conta";
        $token = random_int(100000, 999999);

        $mensagem = "<div style='font-family: Arial, sans-serif;'>";
        $mensagem .= "<h2>Código de verificação:</h2>";
        $mensagem .= "<p style='font-size: 24px; font-weight: bold;'>Token: {$token}</p>";
        $mensagem .= "</div>";

        $mail->msgHTML($mensagem);
        $mail->send();



        $_SESSION['token'] = $token;

        echo json_encode(['error' => false]);
        exit;  
    }

    public function enviarSuporteLogado($texto, $tipoMsg){
        if(!$this->validarLogin()){
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        if($texto == null || $tipoMsg == null){
            json_encode(['error' => true, 'msg' => 'A mensagem e o tipo nao podem ser vazios!']);
            exit;
        }

        $suporte = new Suporte();
        $suporte->setEmail($_SESSION['email']);
        $suporte->setMensagem($texto);
        $suporte->setTipo($tipoMsg);

        if($this->SuporteDAO->enviarMensagem($suporte)){
            echo json_encode(['error' => false]);
            exit;
        }else{
            echo json_encode(['error'=> true,'msg'=> 'Erro no envio da mensagem para o suporte, tente novamente!']);
            exit;
        }
    }

}

?>