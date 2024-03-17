<?php

namespace MacoBackend\Helpers;

use MacoBackend\Models\RoleModel;

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
}