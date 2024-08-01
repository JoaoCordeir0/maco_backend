<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class ArticleReferencesModel extends Opers {

    private $table = 'article_references';
    private $columns = [               
        'article:int:not null',       
        'reference:text:not null',            
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

    public function getReference() 
    {
        return parent::result()->reference;
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