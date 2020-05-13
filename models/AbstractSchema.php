<?php
namespace models;
use \system\System;
abstract class AbstractSchema {
    protected $item = null;
    function __construct($item=null) {
        if ($item){
            $this->item = $item;
        }
        
    }
    function load($item){
        $this->item = $item;
        return $this;
    }
    function item(){
        return $this->item;
    }

    
}