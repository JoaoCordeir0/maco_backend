<?php

namespace MacoBackend\Helpers;

use \Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface as Request;
use MacoBackend\Models\RoleModel;
use MacoBackend\Models\UserModel;

class UserHelper
{    
    /**
     * Função que remove pontos e traços do documento
     * 
     * @param $cpf
     */
    public static function cleanDocument(string $cpf):string
    {
       return trim(str_replace('-', '', str_replace('.', '', $cpf)));
    }    

    /**
     * Função que valida e-mail
     * 
     * @param $email
     */
    public static function validateEmail(string $email):bool
    {
        if (preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email))
            return true;
        else 
            return false;        
    }

    /**
     * Função que retorna o papel do usuário
     * 
     * @param $role
     */
    public static function formatUserRole($role):string 
    {
        switch($role)
        {
            case RoleModel::ADMIN:
                $role = RoleModel::ADMIN . ':ADMIN';
                break;
            case RoleModel::ADVISOR:
                $role = RoleModel::ADVISOR . ':ADVISOR';
                break;
            case RoleModel::AUTHOR:
                $role = RoleModel::AUTHOR . ':AUTHOR';
                break;
        }
        return base64_encode($role);
    }

    /**
     * Função que monta a condição where com base nos parametros passados na url
     * 
     * @param $params
     */
    public static function conditionByList(object $params): string
    {
        if (isset($params->user_id)) {
            return "user.id = " . $params->user_id;   
        }
        else if (isset($params->course_id)) {            
            return "course.id = " . $params->course_id;
        }                      
        else if (isset($params->course_name)) {            
            return "course.name like '%" . $params->course_name . "%'";
        }                      
        return '';       
    }    

    /**
     * Função que gera um JWT com base em um array
     * 
     * @param $data
     */
    public static function generateJWT($data): string
    {
        $data += [
            'iat' => time(),
            'exp' => time() + (60 * 240) // Expira em 5 horas
        ]; 

        return JWT::encode($data, getenv('TOKEN_SECRET'));
    }   
    
    /**
     * Função que valida o nivel de usuário para determinada função
     * 
     * @param $request
     * @param $role
     */
    public static function checkUserRole(Request $request, int $role): bool
    {        
        $userRole = self::getUserInToken($request, 'role');
        $userRole = base64_decode($userRole);
        $userRole = explode(':', $userRole);
        $userRole = $userRole[0];        

        if ($userRole == $role) {
            return false;
        }
        return true;        
    }

    /**
     * Função que valida o nivel de usuário para determinada função
     * 
     * @param $request
     * @param $role
     */
    public static function getUserInToken(Request $request, string $info): string
    {
        $jwt = $request->getHeaderLine('Authorization');
        $jwt = str_replace('Bearer', '', $jwt);
        $jwt = str_replace(' ', '', $jwt);
        
        $user = JWT::decode($jwt, getenv('TOKEN_SECRET'), array_keys(JWT::$supported_algs));
       
        switch($info) {
            case 'id':
                return $user->id;
                break;
            case 'name':
                return $user->name;
                break;
            case 'email':
                return $user->email;
                break;
            case 'ra':
                return $user->ra;
                break;
            case 'role':
                return $user->role;
                break;
        }
        return '';        
    }
}