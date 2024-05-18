<?php

namespace MacoBackend\Helpers;

use MacoBackend\Models\UserCourseModel;

class ArticleHelper
{    
    /**
     * Função que monta a condição where com base nos parametros passados na url
     * 
     * @param $params
     */
    public static function conditionByList(object $params): string
    {
        switch($params) {                    
            // Só ID
            case isset($params->article_id): 
                return "article.id = {$params->article_id}";   
                break;                                
            // Titulo e status
            case isset($params->article_title, $params->article_status): 
                return "article.title like '%{$params->article_title}%' and article.status = {$params->article_status}";
                break;     
            // Status, curso e evento
            case isset($params->course_id, $params->article_status, $params->event_id):
                return "($params->course_id in (select course from article_authors where article = article.id)) and article.status = {$params->article_status} and article.event = {$params->event_id}";
                break;                       
            // Status e evento
            case isset($params->event_id, $params->article_status):
                return "article.event = {$params->event_id} and article.status = {$params->article_status}";
                break;  
            // Curso e evento
            case isset($params->course_id, $params->event_id):
                return "($params->course_id in (select course from article_authors where article = article.id)) and article.event = {$params->event_id}";
                break;                    
            // Só status
            case isset($params->article_status):
                return "article.status = {$params->article_status}";
                break;
            // Só titulo           
            case isset($params->article_title):
                return "article.title like '%{$params->article_title}%'";
                break;
            // Só evento
            case isset($params->event_id):
                return "article.event = {$params->event_id}";
                break;                        
        }        
        return '';       
    }             
    
    /**
     * Função que retorna a condição da consulta de revisores
     * 
     * A condição retorna artigos somente dos cursos no qual o revisor está matriculado
     * 
     * @param $advisorID
     * @param $condition
     */
    public static function getConditionAdvisor($advisorID, $condition): string
    {
        if (strlen($condition) > 5) {
            $condition = " and {$condition}";
        }

        $advisor = new UserCourseModel();
        $advisor->select(['course'])
                ->where("user = {$advisorID}")
                ->get(true);
                
        $queryCourse = '';
        foreach($advisor->result() as $c) {
            $queryCourse .= $c['course'] . ' in (select course from article_authors where article = article.id) or ';
        }
        $queryCourse = '(' . substr($queryCourse, -0, -4) . ')'; 

        return "article.status = 2 and {$queryCourse} " . $condition;
    }

    /**
     * Função que retorna a condição da consulta dos autores
     * 
     * @param $authorID
     * @param $condition
     */
    public static function getConditionAuthor($authorID, $condition): string
    {
        if (strlen($condition) > 5) {
            $condition = " and {$condition}";
        }
        return "(article.status = 1 or article.status = 3) and ({$authorID} in (select user from article_authors where article_authors.article = article.id)) " . $condition;
    }
}
