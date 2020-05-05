<?php
namespace controllers\auth;

class _ extends \controllers\AbstractController {

    function beforeroute($system, $pattern, $handler) {
        parent::beforeroute($system, $pattern, $handler);

    }
    function afterroute($system) {

        // anything you want to run before the route happens for all controllers
        parent::afterroute($system);
    }
    static function routes($system) {

        $system->route("GET|POST @auth_login: /login", "\\controllers\\auth\\LoginController->page");
        $system->route("GET|POST @auth_logout: /logout", "\\controllers\\auth\\LogoutController->page");
        $system->route("GET|POST @auth_forgot: /forgot", "\\controllers\\auth\\ForgotController->page");
        $system->route("GET|POST @auth_reset: /reset", "\\controllers\\auth\\ResetController->page");
        $system->route("GET|POST @auth_register: /register", "\\controllers\\auth\\RegisterController->page");

    }

}