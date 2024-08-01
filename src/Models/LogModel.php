<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class LogModel extends Opers {

    private $table = 'log';
    private $columns = [     
        'log:json:not null',
        'area:varchar(255):not null',         
    ];

    public function __construct() {        
        parent::__construct($this->table, $this->columns, false);
    }   

    public function getID() 
    {
        return parent::result()->id;
    }    

    public function getlog() 
    {
        return parent::result()->log;
    }    
    
    public function getArea() 
    {
        return parent::result()->area;
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