<?php
namespace controllers;

use \system\Output;
use \system\Profiler;
use \system\utilities\Arrays;
use \system\utilities\System;

class HomeController extends AbstractController {
    function beforeroute($system, $pattern, $handler) {
        parent::beforeroute($system, $pattern, $handler);

        // anything you want to run before the route happens for all controllers

    }
    function afterroute($system) {

        // anything you want to run before the route happens for all controllers
        parent::afterroute($system);
    }
    function page() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $format = $this->system->get("PARAMS")['FORMAT'];
        if (strtolower($format) == "json") {
            $this->system->get("OUTPUT")->setFormat(Output::JSON);
        }

        $data = array();
        $data['t'] = date("home H:i:s");
        $data['format'] = $format;

        
        // $this->system->debug($data);

        $data['search'] = isset($_POST['search']) ? $_POST['search'] : "";
        // OR
        $data['search'] = Arrays::getValue($_POST, "search", "search" . date("Hms")); // Arrays::getValue(ARRAY,KEY,DEFAULT_VALUE)

        // $this->system->get("DB")->exec("SELECT * FROM users;");

        // do shit here. add data to $data and throw it at a a template
        // the template is in /Application/Templates/home.twig
        // this allows for this pages data to be available to ajax (just the data is outputed in json)
        // if ajax or ?showdata is appended to the query string
        // if its a normal request the template gets loaded
        $this->render("home.twig", $data);
        $profiler->stop();

        // to say screw the rendering you want json use this instead (if you dont have a template to render)
        // $this->render($data);
    }

}