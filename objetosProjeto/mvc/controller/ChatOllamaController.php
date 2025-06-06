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
        $ollama_url = 'http://host.docker.internal:11434/api/generate';

        // Alterar dados para que fique tudo certo!
        $memoria = 'Contexto: 
        - O número de vagas é 108.
        - O valor por hora na vaga é 10 reais.
        - O estacionamento funciona das 7h às 22h, todos os dias da semana.
        - O estacionamento funciona das 12h às 18h nos fins de semana.
        - Aceitamos pagamentos em dinheiro, cartão de crédito, débito ou pix.
        - O estacionamento é coberto e possui câmeras de segurança em todos os setores.
        - Para emergências, o cliente pode ligar no número (41) 99999-9999.
        - O limite de altura para veículos é de 2,10 metros.
        - Não é permitido deixar o veículo pernoitar.
        - A entrada do estacionamento fica na Rua Desembargador do Prado, número 1983, centro da cidade.

        Instruções: Use o contexto acima apenas para responder às perguntas do usuário. Não ofereça informações adicionais que não foram solicitadas. Responda de forma objetiva e clara.';

        $mensagem = $memoria . "\nA mensagem do usuário inicia aqui:\n". $mensagem;

        $payload = [
            'model' => 'gemma3:1b',
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

        $resultado = file_get_contents($ollama_url, false, $context);

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