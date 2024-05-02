<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class EventsModel extends Opers {

    private $table = 'events';
    private $columns = [     
        'name:varchar(255):not null', 
        'start:datetime:not null',
        'end:datetime:not null',
        'number_characters:int:',        
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

    public function getStatus() 
    {
        return parent::result()->status;
    }  

    public function getCreatedAt() 
    {
        return parent::result()->created_at;
    }
}