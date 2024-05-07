<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class ArticleModel extends Opers {

    private $table = 'article';
    private $columns = [               
        'event:int:not null',    
        'title:varchar(255):not null',         
        'advisors:varchar(999):not null',                
        'co_advisors:varchar(999):',                
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
    
    public function getAdvisors() 
    {
        return parent::result()->advisors;
    }   
    
    public function getCoAdvisors() 
    {
        return parent::result()->co_advisors;
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