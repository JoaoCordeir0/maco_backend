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
        $date = date('Y-m-d');

        if (isset($params->event_id, $params->status)) {
            return "id = {$params->event_id} and status = {$params->status} and '{$date}' BETWEEN DATE(event.start) AND DATE(event.end)";
        }
        if (isset($params->status)) {
            return "status = {$params->status} and '{$date}' BETWEEN DATE(event.start) AND DATE(event.end)";
        }
        if (isset($params->event_id)) {
            return "id = {$params->event_id}";
        }
        return '';       
    }         
}
