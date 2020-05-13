<?php
namespace models\users;

use \models\AbstractSchema;
use models\SchemaInterface;
use System\utilities\System;

class CurrentUserSchema extends AbstractSchema implements SchemaInterface {
    

    function toArray(){
         /**
         * @var UserModel
         */
        $item = $this->item;
        return array(
            "_"=>$item,
            "id"=>$item->getId(),
            "name"=>$item->getName(),
            "email"=>$item->getEmail(),
        );
    }
}