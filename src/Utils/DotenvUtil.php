<?php

namespace MacoBackend\Utils;

use Exception;

class DotenvUtil
{
   public static function load($path): void
   {
        try
        {
            $lines = file($path . '/.env');
            foreach ($lines as $line) {
                if (stripos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);           
                    putenv(sprintf('%s=%s', $key, $value));
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }              
            }
        }
        catch(Exception $e)
        {
            print $e->getMessage();
        }
   }
}