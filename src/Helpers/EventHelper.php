<?php

namespace MacoBackend\Helpers;

class EventHelper
{        
    /**
     * Função que monta a condição where com base nos parametros passados na url
     * 
     * @param $params
     */
    public static function conditionByList(object $params): string
    {
        if (isset($params->status)) {
            return "status = {$params->status}";
        }
        return '';       
    }         
}
