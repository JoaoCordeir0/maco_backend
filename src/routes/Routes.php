<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use MacoBackend\Controllers\UserController;

// Default route
$app->get('/', function (Request $request, Response $response) {       
    $response->getBody()->write(json_encode([
        'Maco backend' => [
            'Devs' => ['João Victor Cordeiro', 'Henrique Magnoli'],
            'Date' => '03/07/2024'
        ]
    ]));     
    return $response;
});

// Login 
$app->post('/user/login', [UserController::class, 'login']);        
