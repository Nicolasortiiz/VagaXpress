<?php
require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use GuzzleHttp\Client;

$app = AppFactory::create();
$app->setBasePath('/gateway.php');


$app->addBodyParsingMiddleware();


$app->any('/api/{servico}', function (Request $request, Response $response, array $args) {
    $servico = $args['servico'];
    $method = $request->getMethod();

    $urls = [
        'usuario' => 'http://localhost:8001/api',
        'veiculo' => 'http://localhost:8001/api',
        'mensagem' => 'http://localhost:8002/api',
        'suporte' => 'http://localhost:8002/api',
        'notaFiscal' => 'http://localhost:8003/api',
        'registro' => 'http://localhost:8004/api',
        'vagaAgendada' => 'http://localhost:8004/api',
        'vagaOcupada' => 'http://localhost:8004/api',
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

    $opcoes = [
        'query' => $queryParams,
        'http_errors' => false
    ];

    if (stripos($contentType, 'application/json') !== false) {
        $opcoes['json'] = $bodyParams;
    } else {
        $opcoes['form_params'] = $bodyParams;
    }

    try {
        $respostaRemota = $client->request($method, "{$url}/{$servico}.php", [
            'query' => $queryParams,
            'form_params' => $bodyParams,
            'http_errors' => false
        ]);

        $response->getBody()->write($respostaRemota->getBody());
        return $response->withStatus($respostaRemota->getStatusCode())
            ->withHeader('Content-Type', $respostaRemota->getHeaderLine('Content-Type'));
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Erro ao acessar o serviço']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->run();
