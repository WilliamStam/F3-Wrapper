<?php
namespace permissions;

use models\AbstractSchema;
use models\SchemaInterface;

class PermissionSchema extends AbstractSchema implements SchemaInterface {
    function toArray(){
        /**
         * @var PermissionModel
         */
        $item = $this->item;
        
        return array(
            "id"=>get_class($item),
            "label"=>$item->getLabel(),
            "description"=>$item->getDescription(),
            "type"=>$item->getType(),
            "group"=>$item->getGroup(),
        );
    }
}