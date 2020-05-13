<?php
namespace permissions;

use models\SchemaInterface;
use system\Collection;

class PermissionModel {
    protected $permissions = array(
        \permissions\admin\Roles::class,
        \permissions\admin\RolesCategories::class,
        \permissions\admin\Users::class,
        \permissions\admin\SystemStatus::class,
    );
    protected $SCHEMA = null; 

    function __construct(){

    $this->schema(new PermissionSchema());
    }
    function schema(SchemaInterface $schema=null){
        if ($schema){
            $this->SCHEMA = $schema;
        }
        $this->SCHEMA->load($this);
        return $this->SCHEMA;
    }
    function getAll(){
        $permissions = new Collection();
        $permissions->schema($this->schema());
        foreach ($this->permissions as $permission){
            $obj = new $permission();

            $permissions->add($obj);
        }
        return $permissions;
    }
}