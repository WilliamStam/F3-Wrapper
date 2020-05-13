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
            $this->system->get("OUTPUT")->setData($data);
            $this->system->get("OUTPUT")->setBody($templateObject->render());

        } else {
            if (is_array($template)) {
                $data = $template;
            }
            $this->system->get("OUTPUT")->setFormat(Output::JSON);
            $this->system->get("OUTPUT")->setData($data);
            $this->system->get("OUTPUT")->setBody($templateObject->render());
        }
    }
}
