<?php
namespace models;

use \system\utilities\System;
use \system\db\Query;

abstract class AbstractModel {
    protected $DB = null;
    protected $SCHEMA = null;

    function __construct($DB_connection = null) {
        $this->system = \Base::instance();
        $this->DB = $DB_connection;
        if (!$this->DB) {
            $this->DB = $this->system->get("DB");
        }

        $this->query(new Query($this->DB));

    }

    function setFromArray($array = array(),$raw=false) {
        foreach ($array as $key => $value) {
            $setter_name = "set_" . $key;
            $setter_name_camel = $this->system->camelcase($setter_name);
            $setter_name_raw = $this->system->camelcase("raw_".$setter_name);


            if (method_exists($this, $setter_name_raw) && $raw) {
                $this->$setter_name_raw($value);
            } else if ($raw){
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            } else if (method_exists($this, $setter_name_camel)) {
                $this->$setter_name_camel($value);
            } else {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    function toArray($schema=null){
        if ($schema){
            $this->schema($schema);
        }
        return $this->schema()->toArray();
    }
    function schema($schema=null){
        if ($schema){
            $this->SCHEMA = $schema;
        }
        $this->SCHEMA->load($this);
        return $this->SCHEMA;
    }


    
    function query($queryObject=null){
        if ($queryObject){
            $this->QUERY = $queryObject;
        }
    
        return $this->QUERY;
    }



}