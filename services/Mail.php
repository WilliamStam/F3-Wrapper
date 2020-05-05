<?php
declare (strict_types = 1);
namespace services;

class Mail {

    protected $smtp = null;

    protected $to = null;
    protected $subject = null;
    protected $body = null;

    function __construct() {
        $system = \Base::instance();

        $this->smtp = new \SMTP(
            $system->get("CONFIG")['SMTP']['HOST'],
            $system->get("CONFIG")['SMTP']['PORT'],
            $system->get("CONFIG")['SMTP']['SCHEME'],
            $system->get("CONFIG")['SMTP']['USERNAME'],
            $system->get("CONFIG")['SMTP']['PASSWORD'],
            $system->get("CONFIG")['SMTP']['CTX'],
        );

        foreach ($system->get("CONFIG")['EMAIL_HEADERS'] as $key=>$value){
            $this->smtp->set($key, $value);
        }

    }

    function send() {
        $this->smtp->set("To", $this->getTo());
        $this->smtp->set("Subject", $this->getSubject());

        return $this->smtp->send($this->getBody());
    }

    /**
     * Get the value of to
     */
    public function getTo() {

        return $this->to;
    }

    /**
     * Set the value of to
     *
     * @return  self
     */
    public function setTo($to) {
        $this->to = $to;

        return $this;
    }

    /**
     * Get the value of subject
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Set the value of subject
     *
     * @return  self
     */
    public function setSubject($subject) {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value of body
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Set the value of body
     *
     * @return  self
     */
    public function setBody($body) {
        $this->body = $body;

        return $this;
    }
}
