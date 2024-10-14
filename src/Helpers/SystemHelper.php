<?php 

namespace MacoBackend\Helpers;

use Exception;
use MacoBackend\Models\LogModel;

final class SystemHelper
{
    /**
    * Retorna o host que a aplicação esta rodando
    *    
    * @return void
    */
    public static function getHost(): string
    {        
        try {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $protocol . $_SERVER['HTTP_HOST'];
            return $host;
        } catch(Exception $e) {            
            return "http://localhost:9090";
        }                                         
    }       
}
