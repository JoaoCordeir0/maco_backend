<?php

namespace MacoBackend\Helpers;

use MacoBackend\Models\CourseModel;

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
    
    /**
     * Pega o nome do curso pelo id
     * 
     * @param $id
     */
    public static function getCourseByID($id)
    {
        $course = new CourseModel();        
        $course->select(['name'])         
               ->where("id = $id")                      
               ->get();

        return $course->getName();
    }
}