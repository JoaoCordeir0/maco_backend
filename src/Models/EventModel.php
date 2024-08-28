<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class EventModel extends Opers {

    private $table = 'event';
    private $columns = [     
        'name:varchar(255):not null', 
        'start:datetime:not null',
        'end:datetime:not null',
        'number_characters:int:',    
        'number_keywords:int:',
        'instructions:text:',    
        'status:boolean:not null', 
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

    public function getStartDate() 
    {
        return parent::result()->start;
    } 

    public function getEndDate() 
    {
        return parent::result()->end;
    }  

    public function getNumberCharacters() 
    {
        return parent::result()->number_characters;
    }       

    public function getNumberKeywords()
    {
        return parent::result()->number_keywords;
    }

    public function getInstructions()
    {
        return parent::result()->instructions;
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