<?php 

namespace App\Models;

use SimpleDB\Opers;

class UserModel extends Opers {
    
    private $table = 'users';
    private $columns = [     
        'nome:varchar(255):not null', 
        'email:varchar(255):not null',        
        'password:varchar(255):not null',
        'status:int:not null'
    ];

    public function __construct() {        
        parent::__construct($this->table, $this->columns);
    }   
}