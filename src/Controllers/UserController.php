<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\UserModel;
use MacoBackend\Services\Services;
use MacoBackend\Helpers\UserHelper;
use MacoBackend\Models\RoleModel;
use MacoBackend\Models\UserCourseModel;
use MacoBackend\Helpers\LogHelper;

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

        LogHelper::log('User', 'add', $request);

        if ($user->result()->status == 'success' && $user_course->result()->status == 'success') {            
            return ResponseController::message($response, $user->result()->status, 'Registration completed successfully');         
        }        
        return ResponseController::message($response, 'error', $user->result()->message . $user_course->result()->message);                     
    }     

    /**
    * Realiza a edição de um usuário
    *    
    * @return Response
    */
    public function edit(Request $request, Response $response)
    {                           
        $parsedBody = $request->getParsedBody();

        $id = $parsedBody['id'];
        unset($parsedBody['id']);

        if ($parsedBody['password'] != '') {
            $parsedBody['password'] = password_hash($parsedBody['password'], PASSWORD_DEFAULT);
        } else {            
            unset($parsedBody['password']);
        }
    
        if (empty($id)) {                        
            return ResponseController::message($response, 'error', 'Missing information');         
        }

        $user = new UserModel();    
        $user->data($parsedBody)
             ->where("id = {$id}")
             ->update();    

        LogHelper::log('User', 'edit', $request);

        if ($user->result()->status == 'success') {            
            return ResponseController::message($response, $user->result()->status, 'Update completed successfully');         
        }                
        return ResponseController::message($response, 'error', $user->result()->message);                     
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

        LogHelper::log('User', 'recover_password', $request);

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
        $params = (object) $request->getQueryParams();     
     
        if (UserHelper::checkUserRole($request, RoleModel::ADMIN) && $params->mode != 'author' && $params->mode != 'advisor') {            
            return ResponseController::message($response, 'error', 'Operation denied! User is not admin'); 
        }              

        $condition = UserHelper::conditionByList($params);

        $users = new UserModel();
        $users->select(['user.*'])                             
              ->where($condition)     
              ->orderby()
              ->get(true);                           
        
        $data = [];
        foreach($users->result() as $user) {        
            $userID = $user['id'];                         
    
            $courses = new UserCourseModel();
            $courses->select(['course.id', 'course.name', 'course.description', 'user_course.created_at'])
                    ->innerjoin('course on course.id = user_course.course')
                    ->where("user = {$userID}")                    
                    ->get(true);

            array_push($data, array_merge($user, [
                'courses' => $courses->result(),
            ]));                                 
        }       
                
        return ResponseController::data($response, (object) $data);
    }  
    
    /**
     * Função que valida um token
     * 
     * @return Response
     */
    public function validToken(Request $request, Response $response, $args): Response
    {
        $token = ($request->getParsedBody())['token'];

        if (empty($token)) {            
            return ResponseController::message($response, 'error', 'Token must not be empty');                
        }

        if (UserHelper::isValidToken($token)) {                         
            return ResponseController::message($response, 'success', 'Valid token');                     
        }
        return ResponseController::message($response, 'error', 'Invalid token');    
    }
}