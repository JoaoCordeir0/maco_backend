<?php

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Casse para lidar com os Responses
 */
class ResponseController
{    
    /**
     * Metodo de mensagens de retorno
     * 
     * @param $response
     * @param $status
     * @param $message
     */
    public static function message(Response $response, string $status, string $message): Response
    {
        $response->getBody()->write(json_encode([
            'status' => $status, 
            'message' => self::filter($message),
        ]));

        return $response;
    }

    /**
     * Metodo que trata retornos de informações do banco
     * 
     * @param $response
     * @param $data
     */
    public static function data(Response $response, Object $data): Response
    {
        $response->getBody()->write(json_encode($data));

        return $response;
    }


    /**
     * Metodo que filtra as mensagens de erros
     * 
     * @param $response
     * @param $data
     */
    public static function filter(string $message): string
    {        
        if (str_contains($message, 'Duplicate entry')) {
            $explode = explode("'", $message);
            $message = 'Informação já cadastrada: ' . $explode[1];            
        }    
        return $message;
    }
}