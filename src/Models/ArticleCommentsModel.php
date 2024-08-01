<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class ArticleCommentsModel extends Opers {

    private $table = 'article_comments';
    private $columns = [     
        'user:int:not null',    
        'article:int:not null',            
        'comment:text:',        
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

    public function getArticle() 
    {
        return parent::result()->article;
    }       

    public function getComment() 
    {
        return parent::result()->comment;
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