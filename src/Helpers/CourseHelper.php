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
        if (isset($params->course_id)) {
            return "id = " . $params->course_id;   
        }
        if (isset($params->course_name)) {            
            return "name like '%" . $params->course_name . "%'";
        }
        return '';       
    }    
}