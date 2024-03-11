<?php 

namespace MacoBackend\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\UserModel;
use MacoBackend\Services\Services;
use MacoBackend\Helpers\UserHelper;

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
        $user->select()        
             ->where("email = '{$email}'")
             ->limit(1)                        
             ->get();  
        
        if (isset($user->result()->id) && password_verify($password, $user->getPassword()))
        {                       
            $response->getBody()->write(json_encode([
                'status' => 'success',                     
                'message' => 'User login success',                   
                'token' => Services::generateJWT(array(
                    'id' => $user->getID(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'ra' => $user->getRA(),                    
                )),                            
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
        $parsedBody = $request->getParsedBody();

        $name = $parsedBody['name'];
        $cpf = $parsedBody['cpf'];
        $email = $parsedBody['email'];
        $password = $parsedBody['password'];           
        $ra = $parsedBody['ra'];           

        if (empty($name) || empty($cpf) || empty($email) || empty($password) || empty($ra))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Missing information']));   
            return $response;
        }

        if (! UserHelper::validateEmail($email))
        {            
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Invalid email']));   
            return $response;
        }

        $user = new UserModel();    
        $user->data([
            'name' => $name,
            'cpf' => UserHelper::cleanDocument($cpf),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'ra' => $ra,
            'lvl' => 2,
            'status' => 1,
        ])->insert();         

        if ($user->result()->status == 'success')
        {
            $response->getBody()->write(json_encode([
                'status' => $user->result()->status,                     
                'message' => 'Registration completed successfully',                                             
            ])); 
        }
        else
        {
            $response->getBody()->write(json_encode([
                'status' => 'error', 'message' => $user->result()->message
            ]));  
        }             

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
            'status' => 'success',                     
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