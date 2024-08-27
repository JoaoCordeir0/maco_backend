<?php 

namespace MacoBackend\Helpers;

use Exception;

final class FileHelper
{
    /**
    * Realiza a formatação de nome de arquivos
    *    
    * @param $name
    */
    public static function formatFileName($name): string
    {        
        $s = preg_replace("/[^a-zA-Z0-9\-_.!*'()\/]/", "", $name);        
        return substr($s, 0, 1024);                               
    }       

    /**
     * Realiza a exclusão de arquivos temporarios
     * 
     * @param $dir
     */
    public static function delFiles($dir): void
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            unlink($dir . $file);
        }
    }
}
