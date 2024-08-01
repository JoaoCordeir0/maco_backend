<?php

use Slim\Factory\AppFactory;

use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Psr7\Response;
use MacoBackend\Helpers\DotenvHelper;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Load env file
 */
DotenvHelper::load(__DIR__ . '/../');

/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 */
$app = AppFactory::create();

/**
 * The routing middleware should be added earlier than the ErrorMiddleware
 * Otherwise exceptions thrown from it will not be handled by the middleware
 */
$app->addRoutingMiddleware();

/**
 * Add Error Middleware
 *
 * @param bool                  $displayErrorDetails -> Should be set to false in production
 * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool                  $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger  
 *
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Middleware to parse the JSON request body
$app->addBodyParsingMiddleware();

// Api Errors
$errorMiddleware->setDefaultErrorHandler(function (Request $request, Throwable $exception) use ($app) {
  $statusCode = 500;
  $response = new Response();
  $response->getBody()->write(json_encode([
    'status' => 'error',
    'message' => $exception->getMessage(),
  ]));

  return $response->withStatus($statusCode);
});

// API Headers
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
    ->withHeader('Access-Control-Allow-Origin', '*')
    ->withHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept')
    ->withHeader('Content-Type', 'application/json')        
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Middleware para tratar solicitações OPTIONS
$app->options('/{routes:.+}', function (Request $request, Response $response) {
  return $response
      ->withHeader('Allow', 'GET, POST, PUT, DELETE, OPTIONS')
      ->withStatus(200);
});

// Api Middleware
$app->add(new Tuupola\Middleware\JwtAuthentication([  
  'ignore' => ['/user/login', '/user/register', '/user/recoverpassword', '/public/*'],    
  'secret' => getenv('TOKEN_SECRET'),
  'secure' => false, // Caso não tiver https é necessario usar false
]));

// Define app routes
require '../src/Routes/Routes.php';

// Run app
$app->run();
