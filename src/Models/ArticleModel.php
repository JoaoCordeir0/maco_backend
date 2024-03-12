<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class ArticleModel extends Opers {

    private $table = 'article';
    private $columns = [     
        'title:varchar(255):not null', 
        'author:varchar(999):not null',        
        'advisor:varchar(999):not null',                
        'keywords:varchar(255):not null',
        'summary:text:not null',
        'status:int:not null',        
    ];

    public function __construct() {        
        parent::__construct($this->table, $this->columns, false);
    }   

    public function getID() 
    {
        return parent::result()->id;
    }    

    public function getTitle() 
    {
        return parent::result()->title;
    }   

    public function getAuthor() 
    {
        return parent::result()->author;
    }   
    
    public function getAdvisor() 
    {
        return parent::result()->advisor;
    }   
    
    public function getKeywords() 
    {
        return parent::result()->keywords;
    }   

    public function getSumary() 
    {
        return parent::result()->sumary;
    }

    public function getStatus() 
    {
        return parent::result()->status;
    }

    public function getCreatedAt() 
    {
        return parent::result()->created_at;
    }
}