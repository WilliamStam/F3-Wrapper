<?php
namespace models;
use \system\System;
abstract class AbstractModel {
    protected $DB = null;

    function __construct($DB_connection = null) {
        $this->system = \Base::instance();
        $this->DB = $DB_connection;
        if (!$this->DB) {
            $this->DB = $this->system->get("DB");
        }
    }

    function set_from_array($array=array()){
        foreach ($array as $key=>$value){
            $setter_name_snake = "set_".$key;
            $setter_name_camel = $this->system->camelcase($setter_name_snake);

            if (method_exists($this,$setter_name_snake)){
                $this->$setter_name_snake($value);
            } else if (method_exists($this,$setter_name_camel)){
                $this->$setter_name_camel($value);
            } else {
                if (property_exists($this,$key)){
                    $this->$key = $value;
                }
            }
        }
    }
}