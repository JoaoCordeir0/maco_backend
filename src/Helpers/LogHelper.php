<?php 

namespace MacoBackend\Helpers;

use Exception;
use MacoBackend\Models\LogModel;

final class LogHelper
{
    /**
    * Realiza a inserção de logs
    *    
    * @return void
    */
    public static function log($area, $action, $request)
    {        
        try {
            $data = [
                'user_id' => UserHelper::getUserInToken($request, 'id'),
                'action' => $action,
                'body' => $request->getParsedBody(),
            ];

            $log = new LogModel();
            $log->data([            
                'area' => $area,
                'log' => json_encode($data),
            ])->insert();  
        } catch(Exception $e) {
            var_dump($e->getMessage());
        }                                         
    }       
}
