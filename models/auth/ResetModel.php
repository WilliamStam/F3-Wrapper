<?php
namespace models\auth;

use \models\AbstractModel;
use \models\users\UserModel;
use \system\utilities\System;

class ResetModel extends AbstractModel {
    use AttemptsTrait;

    protected $code = null;
    protected $user = null;

    protected $errors = array();

    protected $session = null;
    protected $ip = null;
    protected $agent = null;

    protected $attempts = array(
        "type"=>__CLASS__,
        "minutes"=>0,
        "allowed">0,
        "count"=>0,
        "lifespan_minutes"=>0
    );

    function __construct($session) {
        parent::__construct();
        $this->setSession($session);

        // setting some extra info stuff for internal use
        $this->ip = $this->system->ip();
        $this->agent = $this->system->agent();

        $this->attempts['allowed'] = $this->system->get("CONFIG")['AUTH']['RESET']['ATTEMPTS'];
        $this->attempts['minutes'] = $this->system->get("CONFIG")['AUTH']['RESET']['MINUTES'];
        $this->attempts['lifespan_minutes'] = $this->system->get("CONFIG")['AUTH']['RESET']['TOKEN_MINUTES'];
    }

    function generateCode(UserModel $user) : string {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $return =  false;

        $this->setUser($user);
        $this->setCode( md5($this->system->get("SID")."|".$user->getEmail()."|".$user->getSalt()."|".$this->system->get("CONFIG")['SEED'] ."|".uniqid('', true)) );

        $this->DB->exec("
            INSERT IGNORE INTO system_login_codes (
                session_id,
                user_key,
                code,
                timestamp
            ) VALUES (
                :session_id,
                :user_key,
                :code,
                now()
            )
        ", array(
            ":session_id" => $this->session,
            ":user_key" => $this->getUser()->userKey(),
            ":code" => $this->getCode(),
        ));

        $profiler->stop();
        return $this->getCode();
    }
    function checkCode($email,$code){
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $return = false;

        $this->checkAttempts(429,"Too many failed reset attempts");

        $user = (new UserModel())
            ->_where("LOWER(system_users.email) = :email", array(
                ":email" => strtolower($email)
            ))
            ->get();

        if ($user->getId()){
             $user_reset_code = $this->DB->exec("
                SELECT 
                    user_key,
                    code
                FROM 
                    system_login_codes    
                WHERE
                    user_key = :user_key AND code = :code AND used = '0' AND timestamp >= (NOW() - INTERVAL {$this->attempts['lifespan_minutes']} MINUTE)
            ",array(
                ":user_key"=>$user->userKey(),
                ":code"=>$code
            ));

            if (count($user_reset_code)){
                $this->clearAttempts();


                $this->DB->exec("
                    UPDATE
                        system_login_codes 
                    SET used = '1'       
                    WHERE
                        user_key = :user_key AND code = :code
                ",array(
                    ":user_key"=>$user->userKey(),
                    ":code"=>$code
                ));

                $profiler->stop();
                return $user;
            }
        }
       

        $payload = array(
            "email"=>$email,
            "code"=>$code
        );
        
        $this->saveAttempt($payload);


        $profiler->stop();
        return false;
    }


    
    /**
     * Get the value of code
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @return  self
     */
    public function setCode($code) {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser(UserModel $user) {
        $this->user = $user;

        

        return $this;
    }
    
    /**
     * Get the value of session
     */
    public function getSession() {
        return $this->session;
    }

    /**
     * Set the value of session
     *
     * @return  self
     */
    public function setSession($session) {
        $this->session = $session;

        return $this;
    }

    /**
     * Get the value of attempts
     */
    public function getAttempts() {
        return $this->attempts['count'];
    }
}