<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class UserModel extends Opers {

    private $table = 'users';
    private $columns = [     
        'name:varchar(255):not null', 
        'email:varchar(255):not null',        
        'password:varchar(255):not null',
        'status:int:not null'
    ];

    public function __construct() {        
        parent::__construct($this->table, $this->columns);
    }   

    public function getName() 
    {
        return parent::result()->name;
    }

    public function getEmail() 
    {
        return parent::result()->email;
    }

    public function getPassword() 
    {
        return parent::result()->password;
    }

    public function getStatus() 
    {
        return parent::result()->status;
    }
}