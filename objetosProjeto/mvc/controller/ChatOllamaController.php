<?php
require_once __DIR__ . "/../dao/ChatDAO.php";
require_once __DIR__ . "/../model/ChatOllama.php";
require_once __DIR__ . "/../vendor/autoload.php";

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');



class ChatOllamaController
{
    private $chatDAO;

    public function __construct()
    {
        $this->chatDAO = new ChatDAO();
    }

    public function chamarIA($mensagem)
    {
        $ollama_url = 'http://localhost:11434/api/generate';

        $payload = [
            'model' => 'phi4:14b',
            'prompt' => $mensagem,
            'stream' => false
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($payload),
                'timeout' => 20
            ]
        ];

        $context  = stream_context_create($options);

        $resultado = @file_get_contents($ollama_url, false, $context);

        header('Content-Type: application/json');

        if ($resultado === FALSE) {
            http_response_code(500);
            echo json_encode(['resposta' => 'Erro ao comunicar com a IA.']);
            exit;
        }

        $respostaIA = json_decode($resultado, true);

        if ($respostaIA === null) {
            http_response_code(500);
            echo json_encode(['resposta' => 'Erro ao decodificar resposta da IA.']);
            exit;
        }

        $respostaTexto = $respostaIA['response'] ?? 'Não consegui gerar uma resposta.';

        echo json_encode(['resposta' => $respostaTexto]);
    }
}

?>