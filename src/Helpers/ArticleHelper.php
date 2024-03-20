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
        if (isset($params->id)) {
            return "article.id = " . $params->id;   
        }
        else if (isset($params->title, $params->status)) {            
            return "title like '%" . $params->title . "%' and status = " . $params->status;
        }               
        else if (isset($params->title)) {            
            return "title like '%" . $params->title . "%'";
        }
        else if (isset($params->status)) {            
            return "status = " . $params->status;
        }
        return '';       
    }    
}