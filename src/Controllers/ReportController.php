<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Helpers\UserHelper;
use MacoBackend\Models\ArticleModel;
use MacoBackend\Models\EventModel;
use MacoBackend\Models\LogModel;
use MacoBackend\Models\RoleModel;

final class ReportController
{
    /**
    * Realiza a listagem dos logs existentes
    *    
    * @return Response
    */
    public function listLogs(Request $request, Response $response, $args): Response
    {           
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'Operation denied! User is not a admin');            
        }

        $logs = new LogModel();
        $logs->select()             
             ->orderby('id', 'DESC')
             ->limit(50)
             ->get(true);
        
        return ResponseController::data($response, $logs->result());
    }        

    /**
     * Pega submissões por evento
     * 
     * @return Response
     */
    public function listSubmissionsByEvent(Request $request, Response $response, $args): Response
    {
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'Operation denied! User is not a admin');            
        }

        $events = new EventModel();
        $events->select()             
               ->orderby('id', 'DESC')
               ->limit(6)
               ->get(true);

        $data = [];
        foreach($events->result() as $event) {    
            $articles = new ArticleModel();
            $articles->select(['id'])
                     ->where("event = " . $event['id'])
                     ->get();

            array_push($data, [
                'event' => $event['name'],
                'submissions' => $articles->count()
            ]);
        }
        
        return ResponseController::data($response, (object) $data);
    }
}