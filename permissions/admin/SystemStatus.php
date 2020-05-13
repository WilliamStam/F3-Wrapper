<?php
namespace permissions\admin;

use permissions\permissionInterface;

class SystemStatus extends _ implements permissionInterface{

    protected $label = "System Status";
    protected $description = "This permission hides the system status page";
    protected $type = self::ROLE_TYPE_SYSTEM;
    
}
