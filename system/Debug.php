<?php
declare (strict_types = 1);
namespace system;

class Debug {

    protected $args = array();
    protected $file = null;
    protected $line = null;

    function __construct() {

        $args = func_get_args();
        switch (func_num_args()) {
        case 0:
            exit();
            break;
        case 1:
            $args = $args[0];
            break;
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        $backtraceLevel = 1;
        $backtrace = $backtrace[$backtraceLevel];

        if (is_array($args)) {
            $this->args = array(
                "args" => $args,
                "file" => $backtrace['file'],
                "line" => $backtrace['line'],
            );

            $this->json();
        } else {
            $this->args = $args;
            $this->file = $backtrace['file'];
            $this->line = $backtrace['line'];

            $this->dump();
        }
    }

    function json() {
        header("Content-Type: application/json");
        echo json_encode($this->args, JSON_PRETTY_PRINT);

        exit();
    }
    function dump() {
        ini_set("xdebug.var_display_max_children", "-1");
        ini_set("xdebug.var_display_max_data", "-1");
        ini_set("xdebug.var_display_max_depth", "-1");
        header("Content-Type: text/html");
        var_dump($this);
        exit();
    }

}