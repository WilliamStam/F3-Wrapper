<?php
namespace models\users;

use \models\users\UserModel;
use \models\AbstractSchema;
use \models\SchemaInterface;
use \system\utilities\Strings;

class UserSchema extends AbstractSchema implements SchemaInterface {
    

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
            "last_active" => $item->getLastActive(),
            "timeago" => Strings::timesince($item->getLastActive()),
        );
    }
}