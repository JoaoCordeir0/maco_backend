<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Middleware\Middleware;
use MacoBackend\Models\UserModel;
use MacoBackend\Services\Services;

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

    /**
    * Realiza o envio da recuperação de senha caso o email do usuário for encontrado
    *    
    * @return Response
    */
    public function recoverPassword(Request $request, Response $response)
    {                           
        $parsedBody = $request->getParsedBody();

        $email = $parsedBody['email'];        
        
        if (empty($email))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Empty email']));   
            return $response;
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

        $response->getBody()->write(json_encode([
            'status' => 'ok',                     
            'message' => 'Email sent to ' . $email,                        
        ]));          
        
        return $response;
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
}