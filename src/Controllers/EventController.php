<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\EventModel;
use MacoBackend\Helpers\EventHelper;
use MacoBackend\Helpers\LogHelper;
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
        $number_words = $parsedBody['number_words'];
        $number_keywords = $parsedBody['number_keywords'];
        $instructions = $parsedBody['instructions'];          
        $status = $parsedBody['status'];          

        if (empty($name) || empty($start) || empty($end) || empty($number_words) || empty($number_keywords) || empty($instructions)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $event = new EventModel();
        $event->data([
            'name' => $name,
            'start' => $start,
            'end' => $end,           
            'number_words' => $number_words,
            'number_keywords' => $number_keywords,
            'instructions' => $instructions,
            'status' => $status,
        ])->insert();            
        
        LogHelper::log('Evento', 'Adição de evento', $request);

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
        $number_words = $parsedBody['number_words'];          
        $number_keywords = $parsedBody['number_keywords'];
        $instructions = $parsedBody['instructions'];          
        $status = $parsedBody['status'];          

        if (empty($name) || empty($start) || empty($end) || empty($number_words) || empty($number_keywords) || empty($instructions)) {                        
            return ResponseController::message($response, 'error', 'Missing information');            
        }

        $event = new EventModel();
        $event->data([
            'name' => $name, 
            'start' => $start, 
            'end' => $end, 
            'number_words' => $number_words, 
            'number_keywords' => $number_keywords, 
            'instructions' => $instructions, 
            'status' => $status,
        ])->where("id = {$id}")
          ->update();             

        LogHelper::log('Evento', 'Adição de evento', $request);

        if ($event->getStatus() != 'success') {    
            return ResponseController::message($response, 'error', $event->result()->message);         
        }
        return ResponseController::message($response, $event->result()->status, 'Event edited successfully');   
    }  
}