<?php 

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Middleware\Middleware;
use App\Models\UserModel;

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
        $user->where(['email', $email])->select();                

        if ($user->result())
        {           
            $response->getBody()->write(json_encode([
                'status' => 'ok',                     
                'message' => 'User login success',                   
                'token' => Middleware::generateToken(),
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