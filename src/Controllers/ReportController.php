<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Helpers\UserHelper;
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
}