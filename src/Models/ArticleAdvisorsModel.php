<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class ArticleAdvisorsModel extends Opers {

    private $table = 'article_advisors';
    private $columns = [               
        'article:int:not null',    
        'user:int:not null',            
        'is_coadvisor:boolean:not null',
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
    
    public function getIsCoAdivisor() 
    {
        return parent::result()->is_coadvisor;
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