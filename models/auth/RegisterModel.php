<?php
namespace models\auth;

use \models\AbstractModel;
use \models\users\UserModel;
use \system\utilities\System;

class RegisterModel extends AbstractModel {
    use AttemptsTrait;

    protected $email = null;
    protected $data = array();

    protected $errors = array();

    protected $session = null;
    protected $ip = null;
    protected $agent = null;

    protected $attempts = array(
        "type" => __CLASS__,
        "minutes" => 0,
        "allowed" > 0,
        "count" => 0,
    );

    function __construct($session) {
        parent::__construct();
        $this->setSession($session);

        // setting some extra info stuff for internal use
        $this->ip = $this->system->ip();
        $this->agent = $this->system->agent();

        $this->attempts['allowed'] = $this->system->get("CONFIG")['AUTH']['REGISTER']['ATTEMPTS'];
        $this->attempts['minutes'] = $this->system->get("CONFIG")['AUTH']['REGISTER']['MINUTES'];
    }
    function register($email, $data = array()) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $return = false;

        $this->checkAttempts(429,"Too many failed registering attempts");
        
        $email_in_use = (new UserModel())
            ->_where("LOWER(email) = :email",array(":email"=>strtolower($email)))
            ->getCount();
        
            
        if ( $email_in_use != 0 ){
            $this->errors['email'][] = "This email address cant be used";
        }



        if ( !count($this->errors) ){
            $user = new UserModel();
            $user->set_from_array($data);

            $user->save();
        }


        $this->saveAttempt($data);


        $profiler->stop();
        return $this;
    }

    /**
     * Get the value of errors
     */
    public function getErrors() {
        return $this->errors;
    }
    /**
     * Get the value of attempts
     */
    public function getAttempts() {
        return $this->attempts['count'];
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
     * Get the value of email
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }
}