<?php 

namespace MacoBackend\Helpers;

use Exception;
use MacoBackend\Models\LogModel;

final class SystemHelper
{
    /**
    * Retorna o host que a aplicação esta rodando
    *    
    * @return string
    */
    public static function getBackendHost(): string
    {        
        try {
            return getenv('BACKEND_HOST');
        } catch(Exception $e) {            
            return "http://localhost:9090";
        }                                         
    }     
    
    /**
    * Retorna o host que a aplicação esta rodando
    *    
    * @return string
    */
    public static function getFrontendHost(): string
    {        
        try {
            return getenv('FRONTEND_HOST');
        } catch(Exception $e) {            
            return "http://localhost:3000";
        }                                         
    }     
}
