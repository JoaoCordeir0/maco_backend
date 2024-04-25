<?php

namespace MacoBackend\Helpers;

class ArticleHelper
{    
    /**
     * Função que monta a condição where com base nos parametros passados na url
     * 
     * @param $params
     */
    public static function conditionByList(object $params): string
    {
        if (isset($params->article_id)) {
            return "article.id = " . $params->article_id;   
        }
        else if (isset($params->article_title, $params->article_status)) {            
            return "article.title like '%" . $params->article_title . "%' and article.status = " . $params->article_status;
        }               
        else if (isset($params->article_title)) {            
            return "article.title like '%" . $params->article_title . "%'";
        }
        else if (isset($params->article_status)) {            
            return "article.status = " . $params->article_status;
        }
        else if (isset($params->course_id)) {            
            return "course.id = " . $params->course_id;
        }
        else if (isset($params->course_name)) {        
            return "course.name like '%" . $params->course_name . "%'";                
        }
        return '';       
    }    

    /**
     * Função que monta a condição where com base nos parametros passados na url
     * 
     * @param $params
     */
    public static function conditionByListByAdvisor($courses): string
    {
        $where = '';
        foreach($courses as $course) {
            $where .= 'course.id = ' . $course['course']; 
        }
        return $where;       
    }        
}