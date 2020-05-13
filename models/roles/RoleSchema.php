<?php
namespace models\roles;

use \models\users\RoleModel;
use \models\AbstractSchema;
use models\SchemaInterface;

class RoleSchema extends AbstractSchema implements SchemaInterface {
    

    function toArray(){
        /**
         * @var RoleModel
         */
        $item = $this->item;
        return array(
            "_"=>$item,
            "id"=>$item->getId(),
            "role"=>$item->getRole(),
            "description"=>$item->getDescription(),
            "category_id"=>$item->getCategoryId(),
            "category"=>$item->getCategory(),
        );
    }
}