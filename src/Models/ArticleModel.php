<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class ArticleModel extends Opers {

    private $table = 'article';
    private $columns = [     
        'user:int:not null',    
        'course:int:not null',    
        'event:int:not null',    
        'title:varchar(255):not null', 
        'authors:varchar(999):not null',        
        'advisors:varchar(999):not null',                
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

    public function getUser() 
    {
        return parent::result()->user;
    }   

    public function getTitle() 
    {
        return parent::result()->title;
    }   

    public function getAuthors() 
    {
        return parent::result()->authors;
    }   
    
    public function getAdvisors() 
    {
        return parent::result()->advisors;
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