<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\UserModel;
use MacoBackend\Services\Services;
use MacoBackend\Helpers\UserHelper;
use MacoBackend\Models\RoleModel;
use MacoBackend\Models\UserCourseModel;

final class UserController
{
    /**
    * Realiza a validação do login e retorna informações do usuário
    *    
    * @return Response
    */
    public function login(Request $request, Response $response, $args): Response
    {        
        $parsedBody = $request->getParsedBody();

        $email = $parsedBody['email'];
        $password = $parsedBody['password'];                    

        if (empty($email) || empty($password)) {                        
            return ResponseController::message($response, 'error', 'Empty email or password');         
        }

        $user = new UserModel();    
        $user->select()        
             ->where("email = '{$email}'")
             ->limit(1)                        
             ->get();  
        
        if (isset($user->result()->id) && password_verify($password, $user->getPassword()))
        {                   
            $infoUser = array(
                'id' => $user->getID(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'ra' => $user->getRA(),    
                'role' => UserHelper::formatUserRole($user->getRole()), 
            );
            
            $data = (object) [
                'status' => 'success',                     
                'message' => 'User login success',                   
                'token' => UserHelper::generateJWT($infoUser),   
                'user' => $infoUser,                       
            ];            

            return ResponseController::data($response, $data);                         
        }   
        return ResponseController::message($response, 'error', 'Incorrect email or password');                   
    }  

    /**
    * Realiza o cadastro de um usuário
    *    
    * @return Response
    */
    public function register(Request $request, Response $response)
    {                           
        $parsedBody = $request->getParsedBody();

        $name = $parsedBody['name'];
        $cpf = $parsedBody['cpf'];
        $email = $parsedBody['email'];
        $password = $parsedBody['password'];           
        $ra = $parsedBody['ra'];        
        $course = $parsedBody['course'];           

        if (empty($name) || empty($cpf) || empty($email) || empty($password) || empty($ra) || empty($course)) {                        
            return ResponseController::message($response, 'error', 'Missing information');         
        }

        if (! UserHelper::validateEmail($email)) {                         
            return ResponseController::message($response, 'error', 'Invalid email');                     
        }

        $user = new UserModel();    
        $user->data([
            'name' => $name,
            'cpf' => UserHelper::cleanDocument($cpf),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'ra' => $ra,
            'role' => RoleModel::AUTHOR,
            'status' => 1,
        ])->insert();         

        $user_course = new UserCourseModel();
        $user_course->data(['user' => $user->result()->returnid, 'course' => $course])->insert(); 

        if ($user->result()->status == 'success' && $user_course->result()->status == 'success') {            
            return ResponseController::message($response, $user->result()->status, 'Registration completed successfully');         
        }        
        return ResponseController::message($response, 'error', $user->result()->message . $user_course->result()->message);                     
    }     

    /**
    * Realiza o envio da recuperação de senha caso o email do usuário for encontrado
    *    
    * @return Response
    */
    public function recoverPassword(Request $request, Response $response)
    {                           
        $parsedBody = $request->getParsedBody();

        $email = $parsedBody['email'];        

        if (empty($email)) {            
            return ResponseController::message($response, 'error', 'Empty email');                
        }

        $user = new UserModel();    
        $user->select(['id', 'name', 'email'])        
             ->where("email = '{$email}'")             
             ->get();  

        if (isset($user->result()->id))
        {           
            // Cria logica do token e corpo do email
            // ...
            Services::sendMail('title email', 'html email', $user->getEmail(), $user->getName());           
        }  

        return ResponseController::message($response, 'success', 'Email sent to ' . $email);          
    } 

    /**
    * Altera a senha de um usuário
    *    
    * @return Response
    */
    public function changePassword(Request $request, Response $response)
    {                           
        return $response;
    } 

    /**
    * Realiza a listagem dos usuários
    *    
    * @return Response
    */
    public function listUsers(Request $request, Response $response, $args): Response
    {        
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN)) {            
            return ResponseController::message($response, 'error', 'This user is not admin'); 
        }
        
        $params = (object) $request->getQueryParams();     

        $condition = UserHelper::conditionByList($params);

        $user = new UserModel();
        $user->select(['user.*'])                             
             ->where($condition)     
             ->orderby()
             ->get(true);      
             
        $data = [];
        foreach($user->result() as $user) {        
            $userID = $user['id'];                         
    
            $courses = new UserCourseModel();
            $courses->select(['course.id', 'course.name', 'course.description'])
                    ->innerjoin('course on course.id = user_course.course')
                    ->where("user = {$userID}")                    
                    ->get(true);

            array_push($data, array_merge($user, [
                'courses' => $courses->result(),
            ]));                                 
        }       
                
        return ResponseController::data($response, (object) $data);
    }     
}