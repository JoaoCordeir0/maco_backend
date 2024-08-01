<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class ArticleStatusModel extends Opers {

    private $table = 'article_status';
    private $columns = [     
        'name:varchar(255):not null', 
        'description:text:',        
    ];

    public function __construct() {        
        parent::__construct($this->table, $this->columns, false);
    }   

    public function getID() 
    {
        return parent::result()->id;
    }    

    public function getName() 
    {
        return parent::result()->name;
    }   

    public function getDescription() 
    {
        return parent::result()->description;
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