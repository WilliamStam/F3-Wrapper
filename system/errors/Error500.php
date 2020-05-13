<?php

namespace system\errors;

class Error500 extends AbstractErrors {
    function __construct(){
        parent::__construct();
        
        $this->code = 500;
        $this->status = "Internal Error";
        $this->text = "We hit a bridge at high speed";
        $this->sub_text = "A team of highly trained forensic hamsters have been dispatched to scour through the wreckage and attempt to make sense of the situation.";
        $this->template = "_errors/500.twig";
    }

    function output(){

        if (!$this->system->get('DEBUG')) {
            $key = md5($this->error['text'] . "|" . $this->error['trace']);
            $this->logError($key);

            
        }

        return parent::output();
    }
}