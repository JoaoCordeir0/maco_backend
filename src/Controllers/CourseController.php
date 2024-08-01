<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Helpers\CourseHelper;
use MacoBackend\Helpers\LogHelper;
use MacoBackend\Helpers\UserHelper;
use MacoBackend\Models\CourseModel;
use MacoBackend\Models\RoleModel;
use MacoBackend\Models\UserCourseModel;

final class CourseController
{
    /**
    * Realiza a listagem dos cursos
    *    
    * @return Response
    */
    public function listCourses(Request $request, Response $response, $args): Response
    {       
        $params = (object) $request->getQueryParams();     
               
        if (isset($params->user_id)) {
            $course = new UserCourseModel();       
            $course->select(['course.*'])               
                   ->innerjoin('course on user_course.course = course.id')                    
                   ->where("user_course.user = {$params->user_id}")                             
                   ->get(true);   
        } else {
            $condition = CourseHelper::conditionByList($params);
            
            $course = new CourseModel();        
            $course->select()
                   ->where($condition)
                   ->orderby()
                   ->get(true);
        }                                           
        return ResponseController::data($response, $course->result());
    }   

    /**
    * Realiza a listagem dos cursos para a pagina publica de cadastro
    *    
    * @return Response
    */
    public function listCoursesPublic(Request $request, Response $response, $args): Response
    {                          
        $course = new CourseModel();        
        $course->select(['id', 'name'])                
                ->orderby()
                ->get(true);

        return ResponseController::data($response, $course->result());
    }   
    
    /**
    * Realiza a inserção de um curso
    *    
    * @return Response
    */
    public function addCourse(Request $request, Response $response, $args): Response
    {        
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'Operation denied! User is not a admin');
        }

        $parsedBody = $request->getParsedBody();

        $name = $parsedBody['name'];
        $description = $parsedBody['description'];             

        if (empty($name) || empty($description)) {            
            return ResponseController::message($response, 'error', 'Missing information');         
        }

        $course = new CourseModel();
        $course->data([
            'name' => $name,
            'description' => $description,
        ])->insert();        
        
        LogHelper::log('Course', 'add', $request);
        
        if ($course->result()->status != 'success') {    
            return ResponseController::message($response, 'error', $course->result()->message);         
        }
        return ResponseController::message($response, $course->result()->status, 'Course inserted successfully');         
    }  

    /**
    * Realiza a edição de um curso
    *    
    * @return Response
    */
    public function editCourse(Request $request, Response $response, $args): Response
    {        
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Operation denied! User is not a admin']));
            return $response;
        }

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

        LogHelper::log('Course', 'edit', $request);

        if ($course->result()->status != 'success') {    
            return ResponseController::message($response, 'error', $course->result()->message);         
        }
        return ResponseController::message($response, $course->result()->status, 'Course edited successfully');   
    }  
}
