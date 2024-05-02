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
        if (isset($params->event_id, $params->status)) {
            return "id = {$params->event_id} and status = {$params->status}";
        }
        if (isset($params->status)) {
            return "status = {$params->status}";
        }
        if (isset($params->event_id)) {
            return "id = {$params->event_id}";
        }
        return '';       
    }         
}
