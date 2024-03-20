<?php

namespace MacoBackend\Helpers;

class CourseHelper
{    
    /**
     * Função que monta a condição where com base nos parametros passados na url
     * 
     * @param $params
     */
    public static function conditionByList(object $params): string
    {
        if (isset($params->id)) {
            return "id = " . $params->id;   
        }
        if (isset($params->name)) {            
            return "name like '%" . $params->name . "%'";
        }
        return '';       
    }    
}