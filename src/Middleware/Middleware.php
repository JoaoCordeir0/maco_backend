<?php

namespace MacoBackend\Middleware;

use Exception;
use DateTime;

class Middleware
{    
    private $token;

    public function __construct($request)
    {     
        $token = $request->getParsedBody()['token'] != '' ?
            $request->getParsedBody()['token'] : $request->getHeader('Authorization')[0];        

        $this->token = $token;
    }

    public function validateRequest() 
    {       
        if (! $this->validateToken())
            return throw new Exception('Token not provided or incorrect!');
    }    

    public function validateToken() 
    {
        if ($this->token == '')
            return false;

        $now = new DateTime();
        $token = getenv('TOKEN_SECRET') . $now->format('m-d');

        if (base64_decode($this->token) != $token)
            return false;

        return true;
    }    

    public static function generateToken()
    {
        $now = new DateTime();        
        return base64_encode(getenv('TOKEN_SECRET') . $now->format('m-d'));
    }
}