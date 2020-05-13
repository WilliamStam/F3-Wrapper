<?php
declare (strict_types = 1);
namespace system;

use models\SchemaInterface;

class Collection implements \IteratorAggregate {

    private $COLLECTION = array();
    private $SCHEMA = null;

    function add($obj) {
        $this->COLLECTION[] = $obj;

        return $obj;
    }
    function getIterator() {
        return new \ArrayIterator($this->COLLECTION);
    }
    function count() {
        return count($this->COLLECTION);
    }

    function first() {
        return reset($this->COLLECTION);
    }
    function getCollection(){
        return $this->COLLECTION;
    }


    function schema( SchemaInterface $schema=null){
        if ($schema){
            $this->SCHEMA = $schema;
        }
        return $this->SCHEMA;
    }
    function toArray(SchemaInterface $schema=null){

        if ($schema){
            $this->schema($schema);
        }

        $schema = $this->schema();
        $return = array();
        foreach ($this->COLLECTION as $item){

            if (!$schema){
                if (method_exists($item,"schema")){
                    $schema = $item->schema();
                }
                
            }
            $schema->load($item);
            $return[] = $schema->toArray();
        }
        return $return;
    }

}