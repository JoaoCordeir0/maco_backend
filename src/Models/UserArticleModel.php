<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class UserArticleModel extends Opers {

    private $table = 'user_article';
    private $columns = [     
        'user:int:not null', 
        'article:int:not null',        
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

    public function getCreatedAt() 
    {
        return parent::result()->created_at;
    }
}