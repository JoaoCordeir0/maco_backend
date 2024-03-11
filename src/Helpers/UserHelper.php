<?php

namespace MacoBackend\Helpers;
use Exception;

class UserHelper
{    
    /**
     * Função que remove pontos e traços do documento
     * 
     * @param $cpf
     */
    public static function cleanDocument(string $cpf)
    {
       return trim(str_replace('-', '', str_replace('.', '', $cpf)));
    }    

    public static function validateEmail(string $email)
    {
        if (preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email))
            return true;
        else 
            return false;        
    }
}