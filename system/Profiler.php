<?php
declare (strict_types = 1);
namespace system;

class Profiler {
    protected $TIME_START;
    protected $TIME_END;
    protected $MEMORY_START;
    protected $MEMORY_END;
    protected $LABEL;
    protected $COMPONENT;

    function __construct($label = null, $component = null) {
        if ($label) {
            $this->setLable($label);
        }
        if ($label) {
            $this->setComponent($component);
        }

        $this->TIME_START = $this->_time();
        $this->MEMORY_START = $this->_memory();
    }

    function setLable($label) {
        $this->LABEL = $label;
    }
    function setComponent($component) {
        $this->COMPONENT = $component;
    }

    function getComponent() {
        return $this->COMPONENT;
    }

    function _time() {
        return (double) microtime(TRUE);
    }
    function _memory() {
        return (double) memory_get_usage();
    }

    function stop($label = null) {
        if ($label) {
            $this->setLable($label);
        }
        $this->TIME_END = $this->_time();
        $this->MEMORY_END = $this->_memory();

        return $this;

    }
    function getLabel() {
        return $this->LABEL;
    }

    function getTimeStart() {
        return $this->TIME_START;
    }
    function getTimeEnd() {
        return $this->TIME_END;
    }
    function getTime() {
        return ($this->getTimeEnd() - $this->getTimeStart()) * 1000;
    }

    function getMemoryStart() {
        return $this->MEMORY_START;
    }
    function getMemoryEnd() {
        return $this->MEMORY_END;
    }
    function getMemory() {
        return $this->getMemoryEnd() - $this->getMemoryStart();
    }

}