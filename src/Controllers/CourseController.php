<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\CourseModel;

final class CourseController
{
    /**
    * Realiza a listagem dos artigos
    *    
    * @return Response
    */
    public function list(Request $request, Response $response, $args): Response
    {        
        $course = new CourseModel();
        $course->select()
               ->get();              
                
        $response->getBody()->write(json_encode($course->result()));                                     

        return $response;
    }  
}