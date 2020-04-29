<?php
namespace models\auth;

use \models\AbstractModel;
use \models\users\UserModel;
use \system\utilities\System;

trait AttemptsTrait {

    protected function saveAttempt($payload=array()) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        $this->DB->exec("
            INSERT INTO system_attempts (
                session_id,
                type,
                ip,
                agent,
                payload,
                timestamp
            ) VALUES (
                :SID,
                :TYPE,
                :IP,
                :AGENT,
                :PAYLOAD,
                now()
            )
        ", array(
            ":SID" => $this->session,
            ":TYPE" => $this->attempts['type'],
            ":IP" => $this->ip,
            ":AGENT" => $this->agent,
            ":PAYLOAD" => json_encode($payload)
        ));
        
        $profiler->stop();
        return $this;
    }
    protected function checkAttempts($error_code,$error_message="") {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        $attemnpts = $this->DB->exec("
            SELECT 
                count(*) as attempts
            FROM system_attempts
            WHERE (
                session_id = :SESSIONID OR ip = :IP
            ) AND timestamp >= (NOW() - INTERVAL {$this->attempts['minutes']} MINUTE)
            AND type = :TYPE
        ", array(
            ":SESSIONID" => $this->session,
            ":IP" => $this->system->ip(),
            ":TYPE" => $this->attempts['type'],
        ));


        if (count($attemnpts)) {
            $attemnpts = $attemnpts[0]['attempts'];
        } else {
            $attemnpts = 0;
        }

        if ($attemnpts >= $this->attempts['allowed']) {
            $this->system->error($error_code, $error_message);
        }

        $this->attempts['count'] = $attemnpts;
        $profiler->stop();
        return $attemnpts;
    }
    protected function clearAttempts() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        $this->DB->exec("DELETE FROM system_attempts WHERE session_id = :SID AND type = :TYPE",array(":SID"=>$this->session,":TYPE"=>$this->attempts['type']));

        $profiler->stop();
        return $this;
    }
}