<?php

namespace MacoBackend\Helpers;

use MacoBackend\Models\ArticleCommentsModel;
use MacoBackend\Models\ArticleModel;

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
            case isset($params->article_status, $params->course_id, $params->event_id):
                return "article.status = {$params->article_status} and course.id = {$params->course_id} and article.event = {$params->event_id}";
                break;
            // Status e curso
            case isset($params->article_status, $params->course_id):
                return "article.status = {$params->article_status} and course.id = {$params->course_id}";
                break;
            // Evento e status
            case isset($params->event_id, $params->article_status):
                return "article.event = {$params->event_id} and article.status = {$params->article_status}";
                break;
            // Evento e curso
            case isset($params->event_id, $params->course_id):
                return "article.event = {$params->event_id} and course.id = {$params->course_id}";
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
            // Só curso
            case isset($params->course_id):
                return "course.id = {$params->course_id}";
                break;
            // Só nome do curso
            case isset($params->course_name):
                return "course.name like '%{$params->course_name}%'";                
                break;     
        }        
        return '';       
    }             
    
    /**
     * Função que retorna a condição da consulta de revisores
     * 
     * @param $advisorID
     * @param $condition
     */
    public static function getConditionAdvisor($advisorID, $condition): string
    {
        if (strlen($condition) > 5) {
            $condition = " and {$condition}";
        }
        return "article.status = 2 and course.id in (select course from user_course where user = {$advisorID})" . $condition;
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
        return "(article.status = 1 or article.status = 3) and article.user = {$authorID} " . $condition;
    }
}
