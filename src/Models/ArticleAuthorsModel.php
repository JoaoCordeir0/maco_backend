<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class ArticleAuthorsModel extends Opers {

    private $table = 'article_authors';
    private $columns = [               
        'article:int:not null',    
        'user:int:not null',    
        'course:int:not null',            
    ];

    public function __construct() {        
        parent::__construct($this->table, $this->columns, false);
    }   

    public function getID() 
    {
        return parent::result()->id;
    }    

    public function getArticle() 
    {
        return parent::result()->article;
    }   

    public function getUser() 
    {
        return parent::result()->user;
    }   

    public function getCourse() 
    {
        return parent::result()->course;
    }   

    public function getStatus()
    {
        return parent::result()->status;
    }

    public function getReturnID() 
    {
        return parent::result()->returnid;
    }

    public function getCreatedAt() 
    {
        return parent::result()->created_at;
    }
}