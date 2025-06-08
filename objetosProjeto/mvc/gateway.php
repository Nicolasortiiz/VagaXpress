<?php
/*
require 'vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST');
});

$app->any('/api/{service}/{endpoint}', function (Request $request, Response $response, $args) {
    $service = $args['service'];
    $endpoint = $args['endpoint'];

    $servicesMap = [
        //exemplo, mudar ips e portas
        'usuario' => 'http://localhost:8001/usuario.php',
        'estacionamento' => 'http://localhost:8002/estacionamento.php',
        'pagamento' => 'http://localhost:8003/pagamento.php',
    ];

    if (!isset($servicesMap[$service])) {
        $response->getBody()->write(json_encode(['error' => 'Serviço não encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $url = $servicesMap[$service] . '?action=' . $endpoint;
    $method = $request->getMethod();
    $body = $request->getParsedBody();

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    $response->getBody()->write($result);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();

*/




require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->setBasePath('/gateway.php');


$app->addBodyParsingMiddleware();


$app->any('/api/{service}', function (Request $request, Response $response, array $args) {
    $service = $args['service']; 
    $method = $request->getMethod(); 

    $targetFile = __DIR__ . "/api/{$service}.php";

    if (!file_exists($targetFile)) {
        $response->getBody()->write("Serviço {$service} não encontrado.");
        return $response->withStatus(404);
    }


    $_GET = $request->getQueryParams();
    $_POST = $request->getParsedBody() ?? [];

    ob_start();
    include $targetFile;
    $output = ob_get_clean();

    $response->getBody()->write($output);
    return $response;
});

$app->run();

