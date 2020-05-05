<?php
namespace models\auth;

use \models\AbstractModel;
use \models\users\UserModel;
use \system\utilities\System;

class ForgotModel extends AbstractModel {
    use AttemptsTrait;

    protected $code = null;
    protected $user = null;
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

        $this->attempts['allowed'] = $this->system->get("CONFIG")['AUTH']['FORGOT']['ATTEMPTS'];
        $this->attempts['minutes'] = $this->system->get("CONFIG")['AUTH']['FORGOT']['MINUTES'];
    }

    function generateCode(UserModel $user): string {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $return = false;

        $this->checkAttempts(429, "Tried to generate a code too many times");

        $this->setUser($user);
        $this->setCode(md5($this->system->get("SID") . "|" . $user->getEmail() . "|" . $user->getSalt() . "|" . $this->system->get("CONFIG")['SEED'] . "|" . uniqid('', true)));

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
            ":user_key" => $this->user->userKey(),
            ":code" => $this->getCode(),
        ));

        $payload = array(
            "user_key" => $this->user->userKey(),
            "code" => $this->getCode(),
        );

        $this->saveAttempt($payload);

        $profiler->stop();
        return $this->getCode();
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

    /**
     * Get the value of errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Set the value of errors
     *
     * @return  self
     */
    public function setErrors($errors) {
        $this->errors = $errors;

        return $this;
    }
}