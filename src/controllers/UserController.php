<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Middleware\Middleware;
use MacoBackend\Models\UserModel;

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
        
        if (empty($email) || empty($password))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Empty email or password']));   
            return $response;
        }
                          
        $user = new UserModel();    
        $user->select(['id', 'name', 'email'])        
             ->where("email = '{$email}'")
             ->where("password = '{$password}'")             
             ->get();  
             
        if (isset($user->result()->id))
        {           
            $response->getBody()->write(json_encode([
                'status' => 'ok',                     
                'message' => 'User login success',                   
                'token' => Middleware::generateToken(),
                'user' => $user->result(),                
            ]));                        
        }   
        else 
        {
            $response->getBody()->write(json_encode([
                'status' => 'error', 'message' => 'Incorrect email or password'
            ]));                        
        }     
        
        return $response;
    }  

    /**
    * Realiza o cadastro de um usuário
    *    
    * @return Response
    */
    public function register(Request $request, Response $response)
    {                           
        return $response;
    }     
}