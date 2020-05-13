<?php
namespace permissions\admin;

use permissions\permissionInterface;

class RolesCategories extends _ implements permissionInterface {

    protected $label = "Roles Categories";
    protected $description = "The user will be able to admin the system role categories";
    protected $type = self::ROLE_TYPE_SYSTEM;


    
}
