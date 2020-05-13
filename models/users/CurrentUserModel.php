<?php
namespace models\users;

use \system\Collection;
use \system\utilities\System;

use \models\AbstractModel;
use \models\DBfetchTrait;
use \system\db\Write;

class CurrentUserModel extends UserModel {

    function __construct($DB = null) {
        parent::__construct($DB);
        $this->schema(new CurrentUserSchema());
        $this
            ->_select("system_users.*")
            ->_from("system_users INNER JOIN system_sessions ON system_sessions.user_key = CONCAT(system_users.id,'|',system_users.salt)")
        ;
    }
    function get($id = null) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $this
            ->_where("system_sessions.session_id = :session",array(":session"=>$id))
        ;
        $return = parent::get(null);
        
        $profiler->stop();
        return $return;
    }

    
    function save($allow_insert = false) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);


        $values = array(
            "last_active"=>$this->last_active,
        );

        $save = new Write('system_users',$this->DB);
        $save->setWhere("id = :ID",array(":ID" => $this->id));
        $save->setSaveOnDry(false);
        $save->setAudit(false);
        $return = $save->save($values);


        $profiler->stop();
        return $return;
    }
    


    
}

