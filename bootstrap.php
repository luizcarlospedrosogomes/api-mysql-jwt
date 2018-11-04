<?php

require './vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Psr7Middlewares\Middleware\TrailingSlash;
use Monolog\Logger;
use Firebase\JWT\JWT;
use App\v1\Controllers\AuthController;

$configs = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];


$container = new \Slim\Container($configs);

$container['autenticacao'] = function($container) {
    $logger = new Monolog\Logger('autenticacao');
    $logfile = __DIR__ . '/logs/'.date('d-m-Y').'_autenticacao.log';
    $stream = new Monolog\Handler\StreamHandler($logfile, Monolog\Logger::DEBUG);
    $fingersCrossed = new Monolog\Handler\FingersCrossedHandler(
        $stream, Monolog\Logger::INFO);
    $logger->pushHandler($fingersCrossed);
    
    return $logger;
};

$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        return $c['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-Type', 'Application/json')
            ->withHeader("Access-Control-Allow-Methods", implode(",", $methods))
            ->withJson(["message" => "Method not Allowed; Method must be one of: " . implode(', ', $methods)], 405);
    };
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'Application/json')
            ->withJson(['message' => 'Ops!!! Recurso não encontrado. Continue procurando.']);
    };
};

$isDevMode = true;

/**
 * Diretório de Entidades e Metadata do Doctrine
 */
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src/Models/Entity"), $isDevMode);

/**
 * Array de configurações da nossa conexão com o banco
 */
$conn = array(
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'slim-api',
    'user' => 'root',
    'password' => '',
    'charset' => 'utf8'
);

/**
 * Instância do Entity Manager
 */
$entityManager = EntityManager::create($conn, $config);


/**
 * Coloca o Entity manager dentro do container com o nome de em (Entity Manager)
 */
$container['em'] = $entityManager;
$container['secretkey'] = md5("A primeira regra do Clube da Luta é: você não fala sobre o Clube da Luta. A segunda regra do Clube da Luta é: você não fala sobre o Clube da Luta. Terceira regra do Clube da Luta: se alguém gritar Pára, fraquejar, sinalizar, a luta está terminada. Quarta regra: apenas dois caras numa luta.");

$app = new \Slim\App($container);
/**
 * @Middleware Tratamento da / do Request 
 * true - Adiciona a / no final da URL
 * false - Remove a / no final da URL
 * 
 */
$app->add(new TrailingSlash(false));
/*
$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
     
    "users" => [
        "root" => "toor"
    ],
     
    "path" => ["/v1/auth"],
     
    //"passthrough" => ["/auth/liberada", "/admin/ping"],
]));
*/
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "header" => "X-Token",
    "regexp" => "/(.*)/",
    "rules" => [
        new Tuupola\Middleware\JwtAuthentication\RequestPathRule([
            "path" => "/",
            "passthrough" => ["/v1/auth"],
            "ignore" => ["/v1/auth"]
        ]), 
        new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
            "ignore" => ["OPTIONS"]
        ])
    ],
    "realm" => "Protected", 
    "secret" => $container['secretkey'],//Nosso secretkey criado 
    "environment" => ["HTTP_AUTHORIZATION", "REDIRECT_HTTP_AUTHORIZATION"],
    "logger" => $container['autenticacao'],
    //"callback" => function ($request, $response, $arguments) use ($container) {
      //  $container["jwt"] = $arguments["decoded"];
    //},      
    "error" => function ($response, $arguments) {
        $data["status"]  = "error";
        $data["message"] = $arguments["message"];
        $data["decoded"] = $arguments["decoded"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    },
]));

$trustedProxies = ['0.0.0.0', '127.0.0.1'];
$app->add(new RKA\Middleware\ProxyDetection($trustedProxies));


//CORS
$app->add(new Tuupola\Middleware\CorsMiddleware([
    "origin" => ["*"],
    "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE","OPTIONS"],
    "headers.allow" =>  ["Authorization","Accept", "Content-Type", "X-Token", "id_projeto"],
    "headers.expose" => [],
    "credentials" => false,
    "cache" => 86400,
]));  