<?php

namespace MacoBackend\Helpers;

use MacoBackend\Models\ArticleCommentsModel;

class ArticleHelper
{    
    /**
     * Função que monta a condição where com base nos parametros passados na url
     * 
     * @param $params
     */
    public static function conditionByListByAdmin(object $params): string
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
    public static function conditionByListByAdvisorAndAuthor(object $params): string
    {
        if (isset($params->article_id)) {
            return "and article.id = " . $params->article_id;   
        }
        return '';       
    }     

    /**
     * Função que junta os dados dos artigos com os comentários, caso tenha
     * 
     * @param $articles
     */
    public static function joinArticleComments($articles): Object 
    {        
        $data = [];
        foreach($articles as $article) {        
            $articleID = $article['id'];
            $comments = new ArticleCommentsModel();
            $comments->select(['comment'])
                     ->where("article = {$articleID}")
                     ->get(true);  
            
            array_push($data, array_merge($article, [
                'comments' => $comments->result()
            ]));                                 
        }
        return (object) $data;
    }
}
