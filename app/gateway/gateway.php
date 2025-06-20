<?php

header("Access-Control-Allow-Origin: http://vagaxpress.com");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use GuzzleHttp\Client;

$app = AppFactory::create();
$app->setBasePath('/gateway.php');


$app->addBodyParsingMiddleware();
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://vagaxpress.com')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

$app->any('/api/{servico}', function (Request $request, Response $response, array $args) {
    $servico = $args['servico'];
    $method = $request->getMethod();

    $urls = [
        'usuario' => 'http://gestao-veiculos-service:8880/api',
        'veiculo' => 'http://gestao-veiculos-service:8880/api',
        'mensagem' => 'http://notificacoes-service:8881/api',
        'suporte' => 'http://notificacoes-service:8881/api',
        'notaFiscal' => 'http://pagamento-service:8882/api',
        'registro' => 'http://vagas-service:8883/api',
        'vagaAgendada' => 'http://vagas-service:8883/api',
        'vagaOcupada' => 'http://vagas-service:8883/api',
    ];

    $url = $urls[$servico] ?? null;
    if (!$url) {
        $response->getBody()->write(json_encode(['error' => 'Serviço não encontrado.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $client = new Client();

    $queryParams = $request->getQueryParams();
    $bodyParams = $request->getParsedBody() ?? [];
    $contentType = $request->getHeaderLine('Content-Type');
    $cookieHeader = $request->getHeaderLine('Cookie');
    $opcoes = [
        'query' => $queryParams,
        'http_errors' => false,
        'headers' => [
            'Cookie' => $cookieHeader
        ]
    ];

    if (stripos($contentType, 'application/json') !== false) {
        $opcoes['json'] = $bodyParams;
    } else {
        $opcoes['form_params'] = $bodyParams;
    }

    try {
        $respostaRemota = $client->request($method, "{$url}/{$servico}.php", $opcoes);

        if ($respostaRemota->hasHeader('Set-Cookie')) {
            $response = $response->withHeader('Set-Cookie', $respostaRemota->getHeader('Set-Cookie'));
        }
        
        $response->getBody()->write((string) $respostaRemota->getBody());
        return $response->withStatus($respostaRemota->getStatusCode())
            ->withHeader('Content-Type', $respostaRemota->getHeaderLine('Content-Type'));
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Erro ao acessar o serviço']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->run();
