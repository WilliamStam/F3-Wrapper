<?php
namespace models\roles;

use \models\users\RoleModel;
use \models\AbstractSchema;
use models\SchemaInterface;

class RoleCategorySchema extends AbstractSchema implements SchemaInterface {
    

    function toArray(){
        /**
         * @var RoleCategoryModel
         */
        $item = $this->item;
        return array(
            "_"=>$item,
            "id"=>$item->getId(),
            "category"=>$item->getCategory()
        );
    }
}