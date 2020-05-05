<?php
namespace controllers\admin;

class _ extends \controllers\AbstractController {

    function beforeroute($system, $pattern, $handler) {
        parent::beforeroute($system, $pattern, $handler);

    }
    function afterroute($system) {

        // anything you want to run before the route happens for all controllers
        parent::afterroute($system);
    }
    static function routes($system) {

        $system->route("GET|POST @admin_roles: /admin/roles", "\\controllers\\admin\\RolesController->page");

    }

}