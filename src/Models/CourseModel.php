<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class CourseModel extends Opers {

    private $table = 'course';
    private $columns = [     
        'name:varchar(255):not null', 
        'description:text:',        
        'image:varchar(455):', 
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

    public function getDescription() 
    {
        return parent::result()->description;
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