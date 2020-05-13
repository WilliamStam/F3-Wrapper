<?php

namespace system\errors;
use System\utilities\System;
class Error401 extends AbstractErrors {
    function __construct(){
        parent::__construct();

        $this->code = 401;
        $this->status = "Not allowed there";
        $this->text = "Somones being a tad naughty...";
        $this->sub_text = "You arent allowed to view this content. You dont has the athority!";
        $this->template = "_errors/401.twig";
    }

    function output(){
        
        
    
        return parent::output();
    }
}