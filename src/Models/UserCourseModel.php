<?php 

namespace MacoBackend\Models;

use SimpleDB\Opers;

class UserCourseModel extends Opers {

    private $table = 'user_course';
    private $columns = [     
        'user:int:not null', 
        'course:int:not null',        
    ];

    public function __construct() {        
        parent::__construct($this->table, $this->columns, false);
    }   

    public function getID() 
    {
        return parent::result()->id;
    }    

    public function getUser() 
    {
        return parent::result()->user;
    }   

    public function getCourse() 
    {
        return parent::result()->course;
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