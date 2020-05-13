<?php
namespace permissions\admin;

use permissions\permissionInterface;

class Roles extends _ implements permissionInterface {

    protected $label = "Roles";
    protected $description = "This role will allow the user to edit system Roles";
    protected $type = self::ROLE_TYPE_SYSTEM;


    

}
