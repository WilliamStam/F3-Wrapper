<?php

namespace system\errors;

use system\Template;
use System\utilities\System;

abstract class AbstractErrors {
    protected $code;
    protected $status;
    protected $text;
    protected $sub_text;
    protected $template = "_errors/generic.twig";
    protected $error;


    function __construct(){
        $this->system = \Base::instance();

        $error = $this->system->get("ERROR");
            
        $this->setCode($error['code']);
        $this->setStatus($error['status']);
        $this->setText($error['text']);
        $this->setSubText($error['sub_text']);
        $this->setError($error);

        $this->setError($error);
        
    }


    function output(){
        $render = new Template($this->template);

        $render->code = $this->code;
        $render->status = $this->status;
        $render->text = $this->text;
        $render->sub = $this->sub_text;

        $this->system->get("OUTPUT")->setData($render->getData());
        $this->system->get("OUTPUT")->setBody($render->render());

        $this->system->get('PAGE_PROFILER')->stop();
        $this->system->get("OUTPUT")->setProfiler($this->system->get("PROFILER"));
        $this->system->get("OUTPUT")->output();
    }

    function logError($key){
        $key = $this->getCode()."|".$key;
        try {
            /* lets log it to the DB */
            $this->system->get("DB")->exec("
                    INSERT INTO system_errors (
                        `error_key`, `datetime_added`, `datetime_last`, `version`, `code`, `url`, `count`, `message`, `trace`
                    ) VALUES (
                        :KEY,now(),now(),:VERSION, :CODE, :URL, 1, :MESSAGE, :TRACE
                    ) ON DUPLICATE KEY UPDATE
                        `datetime_last` = VALUES(datetime_last),
                        `count` = VALUES(count) + 1
                ", array(
                ":KEY" => $key,
                ":VERSION" => $this->system->get("VERSION"),
                ":CODE" => $this->error['code'],
                ":URL" => $this->system->get("URI"),
                ":MESSAGE" => $this->error['text'],
                ":TRACE" => $this->error['trace'],
            ));

        } catch (\Throwable $e) {
            // we cant exactly let the writing to db throw an error on an error...
        }

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
     * Get the value of status
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of text
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Set the value of text
     *
     * @return  self
     */
    public function setText($text) {
        $this->text = $text;

        return $this;
    }

    /**
     * Get the value of sub_text
     */
    public function getsubText() {
        return $this->sub_text;
    }

    /**
     * Set the value of sub_text
     *
     * @return  self
     */
    public function setsubText($sub_text) {
        $this->sub_text = $sub_text;

        return $this;
    }

    /**
     * Get the value of template
     */
    public function getTemplate() {
        return $this->template;
    }

    /**
     * Set the value of template
     *
     * @return  self
     */
    public function setTemplate($template) {
        $this->template = $template;

        return $this;
    }

    /**
     * Get the value of error
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Set the value of error
     *
     * @return  self
     */
    public function setError($error) {
        $this->error = $error;

        return $this;
    }
}