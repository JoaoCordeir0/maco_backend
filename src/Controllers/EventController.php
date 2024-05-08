<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\EventModel;
use MacoBackend\Helpers\EventHelper;
use MacoBackend\Helpers\UserHelper;
use MacoBackend\Models\RoleModel;

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

        $event = new EventModel();
        $event->select()
              ->where($condition)
              ->orderby('id', 'DESC')
              ->get(true);
        
        return ResponseController::data($response, $event->result());
    }    
    
    /**
    * Realiza a inserção de um novo evento
    *    
    * @return Response
    */
    public function addEvent(Request $request, Response $response, $args): Response
    {        
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'Operation denied! User is not admin'); 
        }

        $parsedBody = $request->getParsedBody();
        
        $name = $parsedBody['name'];
        $start = $parsedBody['start'];          
        $end = $parsedBody['end'];          
        $number_characters = $parsedBody['number_characters'];          
        $status = $parsedBody['status'];          

        if (empty($name) || empty($start) || empty($end) || empty($number_characters)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $event = new EventModel();
        $event->data([
            'name' => $name,
            'start' => $start,
            'end' => $end,           
            'number_characters' => $number_characters,
            'status' => $status,
        ])->insert();            
        
        if ($event->getStatus() != 'success') {            
            return ResponseController::message($response, 'error', $event->debug());                        
        }           
        return ResponseController::message($response, $event->result()->status, 'Event inserted successfully');
    } 

    /**
    * Realiza a edição de um evento
    *    
    * @return Response
    */
    public function editEvent(Request $request, Response $response, $args): Response
    {        
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'Operation denied! User is not admin'); 
        }

        $parsedBody = $request->getParsedBody();        

        $id = $parsedBody['id'];
        $name = $parsedBody['name'];
        $start = $parsedBody['start'];          
        $end = $parsedBody['end'];          
        $number_characters = $parsedBody['number_characters'];          
        $status = $parsedBody['status'];          

        if (empty($id) || empty($name) || empty($start) || empty($end) || empty($number_characters)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $event = new EventModel();
        $event->data(['name' => $name, 'start' => $start, 'end' => $end, 'number_characters' => $number_characters, 'status' => $status])
              ->where("id = {$id}")
              ->update();             

        if ($event->getStatus() != 'success') {    
            return ResponseController::message($response, 'error', $event->result()->message);         
        }
        return ResponseController::message($response, $event->result()->status, 'Event edited successfully');   
    }  
}