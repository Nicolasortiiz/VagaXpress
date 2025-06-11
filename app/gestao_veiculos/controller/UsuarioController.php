<?php
require_once __DIR__ . "/../dao/UsuarioDAO.php";
require_once __DIR__ . "/../model/Usuario.php";
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../controller/VeiculoController.php";
require_once __DIR__ . "/../utils/auth.php";

use PHPMailer\PHPMailer\PHPMailer;
use OTPHP\TOTP;

header('Content-Type: application/json');

date_default_timezone_set('America/Sao_Paulo');



class UsuarioController
{
    private $UsuarioDAO;
    private $VeiculoController;
    private $Auth;
    private $apiToken;
    private $chaveAPI;

    public function __construct()
    {
        $this->chaveAPI = getenv('GOOGLE_API');
        $this->UsuarioDAO = new UsuarioDAO();
        $this->VeiculoController = new VeiculoController();
        $this->Auth = new Auth();
        $this->apiToken = getenv('BOT_API');

    }

    public function encontrarEmail($email, $nome)
    {
        $usuario = new Usuario(0, '', $email, 0.0, '');
        if ($this->UsuarioDAO->encontrarEmail($usuario)) {
            echo json_encode(['error' => true, 'msg' => 'Email ja cadastrado!']);
        } else {

            $this->enviarEmail($email, $nome);
        }
    }

    public function enviarEmail($email, $nome)
    {
        $token = random_int(100000, 999999);

        $_SESSION['token'] = $token;
        $otp = TOTP::create();
        $otp->setLabel('VagaXpress');
        $secret = $otp->getSecret();

        $uri = $otp->getProvisioningUri();
        $qrCode = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($uri) . '&size=300x300&ecc=M';

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->Username = 'projectsmirai0@gmail.com';
        $mail->Password = $this->chaveAPI;
        $mail->setFrom('projectsmirai0@gmail.com', 'VagaXpress');
        $mail->addAddress($email, $nome);
        $mail->Subject = "Confirmação de conta";



        $mensagem = "<div style='font-family: Arial, sans-serif;'>";
        $mensagem .= "<h2>Código de autenticação:</h2>";
        $mensagem .= "<p style='font-size: 24px; font-weight: bold;'>Token: {$token}</p>";

        $mensagem .= "<img src='{$qrCode}' alt='QR Code OTP'>";
        $mensagem .= "</div>";

        $mail->msgHTML($mensagem);
        $mail->send();

        $_SESSION['qr'] = $secret;


        echo json_encode(['error' => false]);
    }

    public function cadastro($nome, $email, $senha, $token)
    {
        if ($token == $_SESSION['token']) {
            $segredo = $_SESSION['qr'];
            $saldo = 0.00;


            $usuario = new Usuario(0, $nome, $email, $saldo, $segredo);
            if ($this->Auth->registro($email, $senha)) {
                $this->UsuarioDAO->cadastrar($usuario);
                unset($_SESSION['token']);
                unset($_SESSION['qr']);
                echo json_encode(['error' => false]);
            } else {
                echo json_encode(['error' => true, 'msg' => 'Erro ao cadastrar a conta!']);
            }
        } else {
            echo json_encode(['error' => true, 'msg' => 'Token inválido!']);

        }
    }

    public function validarOTP($email, $senha, $token, $hora)
    {

        $login = $this->Auth->login($email, $senha);

        if (!$login) {
            echo json_encode(['credError' => true, 'msg' => 'Conta nao encontrada, verifique o e-mail e a senha!']);
            exit;
        }

        $usuario = new Usuario(0, '', $email, 0.0, '');
        $usuario->setSegredo($this->UsuarioDAO->retornaSegredoUsuario($usuario));
        $usuario->setIdUsuario($this->UsuarioDAO->retornaIdUsuario($usuario));

        $otp = TOTP::createFromSecret($usuario->getSegredo());

        if ($otp->at($hora) == $token) {

            if ($usuario->getIdUsuario() > 0) {
                $_SESSION["email"] = $usuario->getEmail();
                $_SESSION["usuario_id"] = $usuario->getIdUsuario();
                echo json_encode(['error' => false]);
            } else {
                echo json_encode(['error' => true, 'msg' => 'Erro ao realizar login, tente novamente!']);
            }

        } else {
            echo json_encode(['error' => true, 'msg' => 'Código incorreto!']);
        }

    }

    public function validarEmail($email)
    {
        $usuario = new Usuario(0, '', $email, 0.0, '');

        if ($this->Auth->procuraUsuario($email)) {

            $nome = $this->UsuarioDAO->encontrarNome($usuario);
            if ($nome) {
                $this->enviarEmail($email, $nome);
            } else {
                echo json_encode(['error' => true, 'msg' => 'Email nao cadastrado!']);
            }

        }else{
            echo json_encode(['error' => true, 'msg' => 'Email nao cadastrado!']);
        }
    }



    public function validarToken($email, $senha, $token)
    {
        if ($token == $_SESSION['token']) {
            $segredo = $_SESSION['qr'];
            unset($_SESSION['qr']);
            $usuario = new Usuario(0, '', $email, 0.0, $segredo);

            if (
                $this->Auth->updateSenha($email, $senha) &&
                $this->UsuarioDAO->updateUsuario($usuario, $segredo)
            ) {
                echo json_encode(['error' => false]);
            } else {
                echo json_encode(['error' => true, 'msg' => 'Erro ao atualizar a conta, tente novamente!']);
            }

        } else {
            echo json_encode(['error' => true, 'msg' => 'Código inválido!']);

        }
    }

    public function validarLoginAutenticacao()
    {
        $pubkey = shell_exec("gpg --armor --export");
        if ($this->Auth->verificarLogin()) {
            if ($this->Auth->obterGruposDoToken() == "Admin") {
                echo json_encode(["login" => 2, "msg" => "Administrador já está logado!"]);
                exit;
            } else {
                echo json_encode(["login" => 1, "msg" => "Usuário já está logado!"]);
                exit;
            }
        } else {
            echo json_encode(["login" => 0, "pubkey" => htmlspecialchars($pubkey)]);
        }
    }

    // Verifica se o usuário está logado e retorna o grupo do usuário
    // retorna chave pública para o usuário 
    public function validarLoginPrincipal()
    {

        $pubkey = shell_exec("gpg --armor --export");
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["login" => 0, "pubkey" => htmlspecialchars($pubkey)]);
            exit;
        }
        $grupo = $this->verificarGrupo();
        if($grupo){
            echo json_encode(["login" => htmlspecialchars($grupo),"pubkey" => htmlspecialchars($pubkey)]);
            exit;
        }else{
            echo json_encode(["login" => 0, "pubkey" => htmlspecialchars($pubkey)]);
        }

    }

    // Retorna 1 pra usuário comum, 2 para admin e false se não estiver logado
    public function verificarGrupo(){
        $grupo = $this->Auth->obterGruposDoToken();

        if(in_array('Admin', $grupo)){
            return 2;
        }else if(in_array('User', $grupo)){
            return 1;
        }
        return false;
    }

    public function adicionarSaldo($valor)
    {

        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }

        $usuario_id = intval($_SESSION["usuario_id"]);
        $valor = str_replace(["R$", " "], "", $valor);
        $valor = floatval(str_replace(",", ".", $valor));
        $usuario = new Usuario();
        $usuario->setIdUsuario($usuario_id);
        $usuario->setSaldo($valor);

        if ($valor <= 0) {
            echo json_encode(["erro" => true, "msg" => "O valor precisa ser maior que zero!"]);
            exit;
        }

        if ($this->UsuarioDAO->adicionarSaldo($usuario)) {
            echo json_encode(["error" => false, "msg" => htmlspecialchars(number_format($valor, 2, ',', '.'))]);
        } else {
            echo json_encode(["error" => true, "msg" => "Erro ao adicionar saldo, tente novamente!"]);
        }


    }

    public function retornarInfosPerfil()
    {
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }

        $usuario_id = intval($_SESSION["usuario_id"]);
        $usuario = new Usuario();
        $usuario->setIdUsuario($usuario_id);

        $resposta = [
            "error" => false,
            "msg" => "",
            "saldo" => null,
            "nome" => null
        ];

        $nome = $this->UsuarioDAO->retornarNome($usuario);
        if ($nome == null) {
            $resposta["error"] = true;
            $resposta["msg"] = "Erro ao retornar nome, tente novamente!";
        } else {
            $resposta["nome"] = $nome;
        }

        $saldo = $this->UsuarioDAO->retornarSaldo($usuario);
        $resposta["saldo"] = htmlspecialchars($saldo);

        echo json_encode($resposta);
    }

    // Realiza o logout do usuário, limpando a sessão
    public function realizarLogout()
    {
        if($this->Auth->logout()){
            echo json_encode(["logout" => true]);
        }else{
            echo json_encode(["logout" => false, "msg" => "Erro ao realizar logout!"]);
        }

    }

    public function validarPagamentoAgendamento($valor, $placa, $id)
    {
        if (!$this->VeiculoController->validarCadastroPlaca($placa, $id)) {
            echo json_encode(["error" => true, "msg" => "Placa não cadastrada!"]);
            exit;
        }

        $usuario = new Usuario();
        $usuario->setIdUsuario($id);
        $saldo = floatval($this->UsuarioDAO->retornarSaldo($usuario));
        if ($valor <= 0) {
            echo json_encode(["error" => true, "msg" => "Erro ao realizar pagamento, tente novamente!"]);
            exit;
        }

        if ($valor > $saldo || $saldo <= 0) {
            echo json_encode(["error" => true, "msg" => "Saldo insuficiente!"]);
            exit;
        }

        echo json_encode(['error' => false]);
        exit;
    }

    
    public function realizarPagamento($valor, $id)
    {
        $usuario = new Usuario();
        $usuario->setIdUsuario($id);
        $saldo = floatval($this->UsuarioDAO->retornarSaldo($usuario));

        if ($saldo < $valor) {
            echo json_encode(['error' => true, 'msg' => 'Saldo insuficiente!']);
            exit;
        }
        if ($valor <= 0) {
            echo json_encode(['error' => true, 'msg' => "Valor inválido!"]);
            exit;
        }
        $novoSaldo = $saldo - floatval($valor);
        $usuario->setSaldo($novoSaldo);
        if ($this->UsuarioDAO->atualizarSaldo($usuario)) {
            echo json_encode(['error' => false]);
            exit;
        } else {
            echo json_encode(['error' => true, 'msg' => 'Erro ao atualizar o saldo!']);
            exit;
        }
    }

    // Função para verificar se o usuário está logado no suporte
    public function validarLoginSuporte()
    {
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => false, "login" => 0]);
            exit;
        }
        echo json_encode(["error" => false, "login" => 1]);
    }

    // Função para remover envio de notificações via Telegram
    public function removerChat()
    {
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        $usuario = new Usuario();
        $usuario->setIdUsuario($_SESSION["usuario_id"]);
        $msg = $this->UsuarioDAO->removerChat($usuario);
        echo json_encode(["error" => false, "msg" => htmlspecialchars($msg)]);

    }

    // Função para adicionar envio de notificações via Telegram
    public function adicionarChat($chatId)
    {
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        $usuario = new Usuario();
        $usuario->setChatId($chatId);
        $usuario->setIdUsuario($_SESSION["usuario_id"]);
        $msg = $this->UsuarioDAO->adicionarChat($usuario);
        echo json_encode(["error" => false, "msg" => htmlspecialchars($msg)]);
        
    }

    // Função para envio de notificações via Telegram
    function enviarNotificacaoTelegram($mensagem) {
        if (!$this->Auth->verificarLogin()) {
            return false;
        }
        $apiToken = $this->apiToken;

        $usuario = new Usuario();
        $usuario->setIdUsuario($_SESSION["usuario_id"]);
        $chatId = $this->UsuarioDAO->retornarChatId($usuario);
        if($chatId == null || $chatId == '') {
            return false;
        }

        $url = "https://api.telegram.org/bot$apiToken/sendMessage";

        $dados = [
            'chat_id' => $chatId,
            'text' => $mensagem
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
        $resposta = curl_exec($ch);
        curl_close($ch);

        return $resposta;
    }

}

?>