<?php 

namespace MacoBackend\Controllers;

use MacoBackend\Helpers\CourseHelper;
use MacoBackend\Helpers\UserHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
    * Realiza a inserção de um curso
    *    
    * @return Response
    */
    public function addCourse(Request $request, Response $response, $args): Response
    {        
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'This user is not a admin');
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
        
        if ($course->result()->status != 'success') {    
            return ResponseController::message($response, 'error', $course->result()->message);         
        }
        return ResponseController::message($response, $course->result()->status, 'Course inserted successfully');         
    }  

    /**
    * Realiza o delete de um curso
    *    
    * @return Response
    */
    public function delCourse(Request $request, Response $response, $args): Response
    {        
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {
            return ResponseController::message($response, 'error', 'This user is not a admin');
        }

        $idCourse = $args['id'];

        if (empty($idCourse)) {            
            return ResponseController::message($response, 'error', 'Missing information');         
        }

        $course = new CourseModel();
        $course->where("id = {$idCourse}")
               ->delete();              
        
        if ($course->result()->status != 'success') {    
            return ResponseController::message($response, 'error', $course->result()->message);         
        }
        return ResponseController::message($response, $course->result()->status, 'Course deleted successfully');       
    }  

    /**
    * Realiza a edição de um curso
    *    
    * @return Response
    */
    public function editCourse(Request $request, Response $response, $args): Response
    {        
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'This user is not a admin']));
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

        if ($course->result()->status != 'success') {    
            return ResponseController::message($response, 'error', $course->result()->message);         
        }
        return ResponseController::message($response, $course->result()->status, 'Course edited successfully');   
    }  
}
