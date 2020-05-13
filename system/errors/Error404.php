<?php

namespace system\errors;

class Error404 extends AbstractErrors {
    function __construct(){
        parent::__construct();
        
        $this->code = 404;
        $this->status = "Page not found";
        $this->text = "The page you seek seems to have wandered off...";
        $this->sub_text = "The page catches have been dispatched, but generally they seem to have a rather bad track record of finding missing pages. We keep them around because of Quota restrictions";
        $this->template = "_errors/404.twig";
    }


    function output(){
        return parent::output();
    }
}