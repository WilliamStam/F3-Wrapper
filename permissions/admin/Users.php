<?php
namespace permissions\admin;

use permissions\permissionInterface;

class Users extends _ implements permissionInterface{

    protected $label = "Users";
    protected $description = "This user will be able to admin other system users";
    protected $type = self::ROLE_TYPE_SYSTEM;
    
}
