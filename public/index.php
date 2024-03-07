<?php

use Slim\Factory\AppFactory;

use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Psr7\Response;

require __DIR__ . '/../vendor/autoload.php';

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

// Errors
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
    ->withHeader('Content-Type', 'application/json')        
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Define app routes
require '../src/routes/Routes.php';

// Run app
$app->run();
