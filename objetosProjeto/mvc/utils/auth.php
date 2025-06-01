<?php
require_once __DIR__ . "/../vendor/autoload.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

class Auth
{
    private $client;
    private $clientSecret;
    private $clientID;
    private $userpoolID;
    public function __construct()
    {
        $env = parse_ini_file(__DIR__ . '/../.env');

        if ($env === false) {
            throw new Exception('Failed to parse .env file');
        }

        $this->clientSecret = $env['COGNITO_CLIENT_SECRET'];
        $this->clientID = $env['COGNITO_CLIENT_ID'];
        $this->userpoolID = $env['COGNITO_USER_POOL_ID'];

        $this->client = new CognitoIdentityProviderClient([
            'region' => $env['COGNITO_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key' => $env['COGNITO_KEY'],
                'secret' => $env['COGNITO_SECRET'],
            ],

        ]);
    }
    private function calculateSecretHash($username)
    {
        $message = $username . $this->clientID;
        $hash = hash_hmac('sha256', $message, $this->clientSecret, true);
        return base64_encode($hash);
    }
    public function registro($email, $password)
    {
        try {
            $this->client->signUp([
                'Username' => $email,
                'Password' => $password,
                'ClientId' => $this->clientID,
                'SecretHash' => $this->calculateSecretHash($email),
                'UserAttributes' => [
                    ['Name' => 'email', 'Value' => $email],
                ],
            ]);

            $this->client->adminConfirmSignUp([
                'UserPoolId' => $this->userpoolID,
                'Username' => $email,
            ]);

            $this->client->adminAddUserToGroup([
                'GroupName' => 'User',
                'UserPoolId' => $this->userpoolID,
                'Username' => $email,
            ]);

            return true;
        } catch (\Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException $e) {
            if ($e->getAwsErrorCode() === 'UsernameExistsException') {
                error_log("Usuário já existe.");
            } else {
                error_log("Erro: " . $e->getAwsErrorMessage());
            }
            return false;
        } catch (Exception $e) {
            error_log("Erro: " . $e->getMessage());
            return false;
        }
    }

    public function login($email, $password)
    {
        error_log($this->clientID);
        try {
            
            $result = $this->client->initiateAuth([
                'AuthFlow' => 'USER_PASSWORD_AUTH',
                'ClientId' => $this->clientID,
                'AuthParameters' => [
                    'SECRET_HASH' => $this->calculateSecretHash($email),
                    'USERNAME' => $email,
                    'PASSWORD' => $password,
                ],
            ]);


            $_SESSION['access_token'] = $result['AuthenticationResult']['AccessToken'];
            $_SESSION['id_token'] = $result['AuthenticationResult']['IdToken'];
            $_SESSION['refresh_token'] = $result['AuthenticationResult']['RefreshToken'];
            return true;

        } catch (\Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException $e) {
            error_log("Erro Cognito: " . $e->getAwsErrorCode() . " - " . $e->getAwsErrorMessage());
            return false;
        } catch (Exception $e) {
            error_log("Erro: " . $e->getMessage());
            return false;
        }
    }

    public function updateSenha($email, $password)
    {
        try {
            $this->client->adminSetUserPassword([
                'UserPoolId' => $this->userpoolID,
                'Username' => $email,
                'Password' => $password,
                'Permanent' => true,
            ]);
            return true;
        } catch (\Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException $e) {
            error_log("Erro Cognito: " . $e->getAwsErrorCode() . " - " . $e->getAwsErrorMessage());
            return false;
        } catch (Exception $e) {
            error_log("Erro: " . $e->getMessage());
            return false;
        }
    }

    public function procuraUsuario($username)
    {
        try {
            $this->client->adminGetUser([
                'UserPoolId' => $this->userpoolID,
                'Username' => $username
            ]);
            return true;
        } catch (\Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException $e) {
            if ($e->getAwsErrorCode() === 'UserNotFoundException') {
                return false;
            } else {
                error_log("Erro Cognito: " . $e->getAwsErrorCode() . " - " . $e->getAwsErrorMessage());
                return false;
            }
        } catch (Exception $e) {
            error_log("Erro geral: " . $e->getMessage());
            return false;
        }
    }


    public function obterGruposDoToken()
    {
        $idToken = $_SESSION['id_token'];
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            return false;
        }

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        if (isset($payload['cognito:groups'])) {
            return $payload['cognito:groups'];
        } else {
            return false;
        }
    }

    public function tokenExpirado(): bool
    {
        $idToken = $_SESSION['id_token'];
        $partes = explode('.', $idToken);

        if (count($partes) !== 3) {
            throw new InvalidArgumentException("Token JWT inválido: número incorreto de partes.");
        }

        $payloadBase64 = strtr($partes[1], '-_', '+/');
        $payloadJson = base64_decode($payloadBase64, true);
        if ($payloadJson === false) {
            throw new RuntimeException("Falha ao decodificar o payload do token.");
        }

        $payload = json_decode($payloadJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Erro ao parsear JSON do payload: " . json_last_error_msg());
        }

        if (!isset($payload['exp'])) {
            throw new UnexpectedValueException("Token JWT sem o campo 'exp' de expiração.");
        }

        $agora = time();
        return $agora >= $payload['exp'];
    }

    public function retirarSubIdToken()
{
    $idToken = $_SESSION['id_token'] ?? null;
    $parts = explode('.', $idToken);

    $payload = base64_decode(strtr($parts[1], '-_', '+/'));

    $claims = json_decode($payload, true);

    return $claims['sub'] ?? null;
}

    public function atualizarToken()
    {
        $hash = $this->calculateSecretHash($this->retirarSubIdToken());
        $token = $_SESSION['refresh_token'] ?? null;
        $result = $this->client->initiateAuth([
            'AuthFlow' => 'REFRESH_TOKEN_AUTH',
            'ClientId' => $this->clientID,
            'AuthParameters' => [
                'REFRESH_TOKEN' => $token,
                'SECRET_HASH' => $hash,
            ],
        ]);


        $_SESSION['access_token'] = $result['AuthenticationResult']['AccessToken'];
        $_SESSION['id_token'] = $result['AuthenticationResult']['IdToken'];
    }

    public function logout()
    {
        $token = $_SESSION['access_token'];
        try {
            $this->client->globalSignOut([
                'AccessToken' => $token
            ]);
            session_unset();
            session_destroy();
            return true;
        } catch (Exception $e) {
            error_log("Erro: " . $e->getMessage());
            return false;
        }
    }

    public function verificarLogin()
    {
        if (
            isset($_SESSION["access_token"]) && isset($_SESSION["id_token"]) && isset($_SESSION["refresh_token"]) &&
            isset($_SESSION["usuario_id"]) && isset($_SESSION["email"])
        ) {
            if (!$this->tokenExpirado()) {
                $this->atualizarToken();
                return true;
            } else {
                $this->logout();
                return false;
            }
        } else {
            session_unset();
            session_destroy();
            return false;
        }
    }

    public function deletarUsuario($email)
    {
        try {
            $this->client->adminDeleteUser([
                'UserPoolId' => $this->userpoolID,
                'Username' => $email,
            ]);

            return true;

        }catch(Exception $e){
            error_log("Erro: " . $e->getMessage());
            return false;
        }

    }

}





?>