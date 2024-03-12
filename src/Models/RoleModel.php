<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class RoleModel extends Opers {

    const ADMIN = 1;
    const ADVISOR = 2;
    const AUTHOR = 3;

    private $table = 'role';
    private $columns = [     
        'description:varchar(255):not null',         
    ];

    public function __construct() {        
        parent::__construct($this->table, $this->columns, false);
    }   

    public function getID() 
    {
        return parent::result()->id;
    }    

    public function getDescription() 
    {
        return parent::result()->description;
    }       

    public function getCreatedAt() 
    {
        return parent::result()->created_at;
    }
}