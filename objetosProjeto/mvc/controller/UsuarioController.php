<?php
require_once __DIR__ . "/../controller/NotaFiscalController.php";
require_once __DIR__ . "/../dao/UsuarioDAO.php";
require_once __DIR__ . "/../model/Usuario.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use OTPHP\TOTP;

session_start();
date_default_timezone_set('America/Sao_Paulo');



class UsuarioController
{
    private $UsuarioDAO;
    private $NotaFiscalController;

    public function __construct()
    {
        $this->UsuarioDAO = new UsuarioDAO();
        $this->NotaFiscalController = new NotaFiscalController();
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

    public function enviarEmail($email, $nome, $token = false)
    {

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
        $mail->Password = 'gyzc stjy qumj kgza';
        $mail->setFrom('projectsmirai0@gmail.com', 'VagaXpress');
        $mail->addAddress($email, $nome);
        $mail->Subject = "Confirmação de conta";

        $token = random_int(100000, 999999);

        $mensagem = "<div style='font-family: Arial, sans-serif;'>";
        $mensagem .= "<h2>Código de autenticação:</h2>";
        $mensagem .= "<p style='font-size: 24px; font-weight: bold;'>Token: {$token}</p>";

        $mensagem .= "<img src='{$qrCode}' alt='QR Code OTP'>";
        $mensagem .= "</div>";

        $mail->msgHTML($mensagem);
        $mail->send();


        $_SESSION['qr'] = $secret;
        $_SESSION['token'] = $token;

        echo json_encode(['error' => false]);
    }

    public function cadastro($nome, $email, $senha, $token)
    {
        if ($token == $_SESSION['token']) {
            $segredo = $_SESSION['qr'];
            $saldo = 0.00;
            unset($_SESSION['token']);
            unset($_SESSION['qr']);

            $usuario = new Usuario(0, $nome, $email, $saldo, $segredo);
            if ($this->UsuarioDAO->cadastrar($usuario, $senha)) {
                echo json_encode(['error' => false]);
            } else {
                echo json_encode(['error' => true, 'msg' => 'Erro ao cadastrar a conta!']);
            }
        } else {
            echo json_encode(['error' => true, 'msg' => 'Token inválido!']);

        }
    }

    public function validarConta($email, $senha)
    {

        $usuario = new Usuario(0, '', $email, 0.0, '');
        if ($this->UsuarioDAO->validarConta($usuario, $senha)) {
            echo json_encode(['error' => false]);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Conta nao encontrada, verifique o e-mail e a senha!']);
        }
    }

    public function validarOTP($email, $token, $hora)
    {
        // tirar usuário teste
        if ($email === "teste") {
            $email = "teste@mail.com";
            $usuario = new Usuario(0, '', $email, 0.0, '');
            $id = $this->UsuarioDAO->retornaIdUsuario($usuario);
            if ($id > 0) {
                $_SESSION["email"] = $email;
                $_SESSION["usuario_id"] = $id;
                $_SESSION["ultima_atividade"] = time();
                echo json_encode(['error' => false]);
            } else {
                echo json_encode(['error' => true, 'msg' => 'Erro ao realizar login, tente novamente!']);
            }
        }

        $usuario = new Usuario(0, '', $email, 0.0, '');
        $usuario->setSegredo($this->UsuarioDAO->retornaSegredoUsuario($usuario));
        $usuario->setIdUsuario($this->UsuarioDAO->retornaIdUsuario($usuario));

        $otp = TOTP::createFromSecret($usuario->getSegredo());

        if ($otp->at($hora) == $token) {

            if ($usuario->getIdUsuario() > 0) {
                $_SESSION["email"] = $usuario->getEmail();
                $_SESSION["usuario_id"] = $usuario->getIdUsuario();
                $_SESSION["ultima_atividade"] = time();
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
        $nome = $this->UsuarioDAO->encontrarNome($usuario);
        if ($nome) {
            $this->enviarEmail($email, $nome);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Email nao cadastrado!']);
        }
    }



    public function validarToken($email, $senha, $token)
    {

        if ($token == $_SESSION['token']) {
            $segredo = $_SESSION['qr'];
            unset($_SESSION['qr']);
            $usuario = new Usuario(0, '', $email, 0.0, $segredo);
            if ($this->UsuarioDAO->updateUsuario($usuario, $senha, $segredo)) {
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
        if (isset($_SESSION["email"]) && isset($_SESSION["ultima_atividade"]) && isset($_SESSION["usuario_id"])) {
            $email = $_SESSION["email"];
            $ultima_atividade = $_SESSION["ultima_atividade"];

            if (time() - $ultima_atividade > 3600) {
                session_unset();
                session_destroy();

                echo json_encode(["login" => 0, "pubkey" => htmlspecialchars($pubkey)]);
                exit;
            }
            if ($email == "admin@vagaxpress.com") {
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

    public function validarLoginPrincipal()
    {
        $pubkey = shell_exec("gpg --armor --export");
        if (isset($_SESSION["email"]) && isset($_SESSION["ultima_atividade"]) && isset($_SESSION["usuario_id"])) {
            $email = $_SESSION["email"];
            $ultima_atividade = $_SESSION["ultima_atividade"];
        } else {
            echo json_encode(["login" => 0, "pubkey" => htmlspecialchars($pubkey)]);
            exit;
        }

        if (time() - $ultima_atividade > 3600) {
            session_unset();
            session_destroy();
            echo json_encode(["login" => 0, "msg" => "Sessão expirada", "pubkey" => htmlspecialchars($pubkey)]);
            exit;
        }

        if ($email == "admin@vagaxpress.com") {
            echo json_encode(["login" => 2, "pubkey" => htmlspecialchars($pubkey)]);
            exit;
        } else {
            echo json_encode(["login" => 1, "pubkey" => htmlspecialchars($pubkey)]);
            exit;
        }
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

    public function adicionarSaldo($valor)
    {

        if (!$this->validarLogin()) {
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
            echo json_encode(["error" => false, "msg" => number_format($valor, 2, ',', '.')]);
        } else {
            echo json_encode(["error" => true, "msg" => "Erro ao adicionar saldo, tente novamente!"]);
        }


    }

    public function retornarInfosPerfil()
    {
        if (!$this->validarLogin()) {
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
            "placas" => null,
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
        $resposta["saldo"] = $saldo;

        $placas = $this->UsuarioDAO->retornarPlacas($usuario);
        $resposta["placas"] = $placas;

        echo json_encode($resposta);
    }

    public function realizarLogout()
    {
        session_unset();
        session_destroy();
        echo json_encode(["logout" => true]);
    }


}

?>