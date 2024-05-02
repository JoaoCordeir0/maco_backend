<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\EventsModel;
use MacoBackend\Helpers\EventHelper;

final class EventController
{
    /**
    * Realiza a listagem dos eventos existentes
    *    
    * @return Response
    */
    public function listEvents(Request $request, Response $response, $args): Response
    {           
        $params = (object) $request->getQueryParams();  

        $condition = EventHelper::conditionByList($params);  

        $event = new EventsModel();
        $event->select()
              ->where($condition)
              ->orderby('id', 'DESC')
              ->get(true);
        
        return ResponseController::data($response, $event->result());
    }     
}