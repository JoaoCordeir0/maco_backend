<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class UserModel extends Opers {

    private $table = 'user';
    private $columns = [     
        'name:varchar(255):not null', 
        'cpf:varchar(30):not null',        
        'email:varchar(255):not null',                
        'password:varchar(255):not null',
        'ra:varchar(30):not null',
        'lvl:int:not null',
        'status:int:not null'
    ];

    public function __construct() {        
        parent::__construct($this->table, $this->columns);
    }   

    public function getID() 
    {
        return parent::result()->id;
    }

    public function getName() 
    {
        return parent::result()->name;
    }

    public function getCpf() 
    {
        return parent::result()->cpf;
    }

    public function getEmail() 
    {
        return parent::result()->email;
    }

    public function getPassword() 
    {
        return parent::result()->password;
    }

    public function getRA() 
    {
        return parent::result()->ra;
    }

    public function getLvl() 
    {
        return parent::result()->lvl;
    }

    public function getStatus() 
    {
        return parent::result()->status;
    }
}