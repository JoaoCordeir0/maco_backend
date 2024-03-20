<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HomeController
{
    /**
    * Home da API
    *    
    * @return Response
    */
    public function home(Request $request, Response $response, $args): Response
    {        
        $response->getBody()->write(json_encode([
            'Maco backend' => [
                'Devs' => ['João Victor Cordeiro', 'Henrique Magnoli'],
                'Date' => '03/03/2024'
            ]
        ]));     

        return $response;       
    }  

    /**
    * API ping
    *    
    * @return Response
    */
    public function ping(Request $request, Response $response, $args): Response
    {               
        $response->getBody()->write(json_encode([
            'status' => 'ok'
        ]));     
        
        return $response;
    }  
}