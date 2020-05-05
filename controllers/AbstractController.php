<?php

namespace controllers;

use system\Output;
use system\Template;

abstract class AbstractController extends _ {
    protected $system;
    protected $root;

    function render($template = "", $data = array()) {

        // $data['USER'] = $this->system->get("USER");

        if (is_string($template)) {
            $templateObject = new Template($template);
            foreach ($data as $key => $value) {
                $templateObject->$key = $value;
            }
            $this->system->set("DATA", $data);
            $this->system->set("BODY", $templateObject->render());
        } else {
            if (is_array($template)) {
                $data = $template;
            }
            $this->system->set("FORMAT", Output::JSON);
            $this->system->set("DATA", $data);
        }
    }
}
