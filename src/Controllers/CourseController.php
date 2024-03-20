<?php 

namespace MacoBackend\Controllers;

use MacoBackend\Helpers\CourseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\CourseModel;

final class CourseController
{
    /**
    * Realiza a listagem dos cursos
    *    
    * @return Response
    */
    public function list(Request $request, Response $response, $args): Response
    {   
        $params = (object) $request->getQueryParams();     

        $condition = CourseHelper::conditionByList($params);

        $course = new CourseModel();
        
        $course->select()
               ->where($condition)
               ->orderby()
               ->get(true);
                
        $response->getBody()->write(json_encode($course->result()));                                     

        return $response;
    }  

    /**
    * Realiza a inserção de um curso
    *    
    * @return Response
    */
    public function add(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $name = $parsedBody['name'];
        $description = $parsedBody['description'];             

        if (empty($name) || empty($description))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Missing information']));   
            return $response;
        }

        $course = new CourseModel();
        $course->data([
            'name' => $name,
            'description' => $description,
        ])->insert();              
        
        if ($course->result()->status == 'success')
        {
            $response->getBody()->write(json_encode([
                'status' => $course->result()->status,                     
                'message' => 'Course inserted successfully',                                             
            ])); 
        }
        else
        {
            $response->getBody()->write(json_encode([
                'status' => 'error', 'message' => $course->result()->message
            ]));  
        }         
        
        return $response;
    }  

    /**
    * Realiza o delete de um curso
    *    
    * @return Response
    */
    public function del(Request $request, Response $response, $args): Response
    {        
        $idCourse = $args['id'];

        if (empty($idCourse))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Missing information']));   
            return $response;
        }

        $course = new CourseModel();
        $course->where("id = {$idCourse}")
               ->delete();              
        
        if ($course->result()->status == 'success')
        {
            $response->getBody()->write(json_encode([
                'status' => $course->result()->status,                     
                'message' => 'Course deleted successfully',                                             
            ])); 
        }
        else
        {
            $response->getBody()->write(json_encode([
                'status' => 'error', 'message' => $course->result()->message
            ]));  
        }         
        
        return $response;
    }  

    /**
    * Realiza a edição de um curso
    *    
    * @return Response
    */
    public function edit(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $id = $parsedBody['id'];
        $name = $parsedBody['name'];
        $description = $parsedBody['description'];             

        if (empty($id) || empty($name) || empty($description))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Missing information']));   
            return $response;
        }

        $course = new CourseModel();
        $course->data(['name' => $name, 'description' => $description])
               ->where("id = {$id}")
               ->update();              
        
        if ($course->result()->status == 'success')
        {
            $response->getBody()->write(json_encode([
                'status' => $course->result()->status,                     
                'message' => 'Course edited successfully',                                             
            ])); 
        }
        else
        {
            $response->getBody()->write(json_encode([
                'status' => 'error', 'message' => $course->result()->message
            ]));  
        }         
        
        return $response;
    }  
}
