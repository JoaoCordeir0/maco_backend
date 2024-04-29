<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\EventSettingsModel;

final class EventController
{
    /**
    * Realiza a listagem dos eventos existentes
    *    
    * @return Response
    */
    public function listEvents(Request $request, Response $response, $args): Response
    {               
        $event = new EventSettingsModel();
        $event->select()
              ->orderby('id', 'DESC')
              ->get(true);
        
        return ResponseController::data($response, $event->result());
    }     
}