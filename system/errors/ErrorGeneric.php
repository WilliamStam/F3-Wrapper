<?php

namespace system\errors;

class ErrorGeneric extends AbstractErrors {
    function __construct(){
        parent::__construct();
        
        $this->status = "An error occured";
        $this->text = "Something happened. something else should of occured but it didnt";
        $this->sub_text = "Our junior forensic hamsters have been alerted, if they dont come right it will be escalated, but for the time being, we are as shocked as you are";
        $this->template = "_errors/generic.twig";
    }

    function output(){

        if (!$this->system->get('DEBUG')) {
            $key = md5($this->error['text'] . "|" . $this->error['trace']);
            $this->logError($key);

            
        }


        return parent::output();
    }
}