<?php
namespace models\users;

use \system\Collection;
use \system\utilities\System;

use \models\AbstractModel;
use \models\DBfetchTrait;


class CurrentUserModel extends UserModel {

    function __construct($DB = null) {
        parent::__construct($DB);
        $this
            ->_select("system_users.*")
            ->_from("system_users INNER JOIN system_sessions ON system_sessions.user_key = CONCAT(system_users.id,'|',system_users.salt)")
        ;
    }
    function get($id = null) {
        
        $this
            ->_where("system_sessions.session_id = :session",array(":session"=>$id))
        ;
        return parent::get(null)->fetchPermissions();
    }

    

    


    
}