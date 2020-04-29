<?php
namespace models\auth;

use \models\AbstractModel;
use \models\users\UserModel;
use \system\utilities\System;

class LoginModel extends AbstractModel {
    use AttemptsTrait;

    protected $session = null;
    protected $user_key = null;
    protected $ip = null;
    protected $agent = null;
    protected $attempts = array(
        "type"=>__CLASS__,
        "minutes"=>0,
        "allowed">0,
        "count"=>0
    );

    function __construct($session) {
        parent::__construct();
        $this->setSession($session);

        // setting some extra info stuff for internal use
        $this->ip = $this->system->ip();
        $this->agent = $this->system->agent();

        $this->attempts['allowed'] = $this->system->get("CONFIG")['LOGIN']['ATTEMPTS'];
        $this->attempts['minutes'] = $this->system->get("CONFIG")['LOGIN']['MINUTES'];
    }

    function login($username, $password) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $return = false;

        $this->checkAttempts(429,"Too many failed login attempts");

        $user = (new UserModel())
            ->_where("LOWER(users.email) = :email", array(
                ":email" => strtolower($username)
            ))
            ->get()
        ;


        $result = password_verify($password, $user->getPassword());
        // System::debug(['password'=>$password,'saved'=>$user->getPassword(),'result'=>$result]);
        if ($result) {
            $return = $this->success($user);
        } else {
            //INFO: should maybe mask the attempted password?
            $payload = array(
                "username"=>$username,
                "password"=>$password
            );
            
            $this->saveAttempt($payload);
        }

        $profiler->stop();
        return $return;
    }
    protected function success($user){
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        // INFO: we clear out the attemps by this session
        $this->clearAttempts();


        $user_key = $user->user_key();
        $this->DB->exec("
            UPDATE system_sessions SET 
                user_key = :user_key 
            WHERE session_id = :session
        ",array(
            ":user_key"=>$user_key,
            ":session"=>$this->session
        ));
        $profiler->stop();
        return $user;
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
     * Get the value of user_key
     */
    public function getuserKey() {
        return $this->user_key;
    }

    /**
     * Set the value of user_key
     *
     * @return  self
     */
    public function setuserKey($user_key) {
        $this->user_key = $user_key;

        return $this;
    }



    /**
     * Get the value of attempts
     */
    public function getAttempts() {
        return $this->attempts['count'];
    }

    /**
     * Set the value of attempts
     *
     * @return  self
     */
    public function setAttempts($attempts) {
        $this->attempts = $attempts;

        return $this;
    }
}