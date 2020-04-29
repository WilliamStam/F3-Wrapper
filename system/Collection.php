<?php
declare (strict_types = 1);
namespace system;

class Collection implements \IteratorAggregate {

    private $COLLECTION = array();

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

}