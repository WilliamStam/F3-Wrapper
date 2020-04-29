<?php
namespace controllers\auth;
use \system\Assets;
class _ extends \controllers\AbstractController {
    
    function beforeroute($system, $pattern, $handler) {
        parent::beforeroute($system, $pattern, $handler);
       

    }
    function afterroute($system) {

        // anything you want to run before the route happens for all controllers
        parent::afterroute($system);
    }
    static function routes($system) {

        $system->route("GET|POST @auth_login: /login", "\\Controllers\\Auth\\LoginController->page");
        $system->route("GET|POST @auth_logout: /logout", "\\Controllers\\Auth\\LogoutController->page");
        $system->route("GET|POST @auth_forgot: /forgot", "\\Controllers\\Auth\\ForgotController->page");
        $system->route("GET|POST @auth_reset: /reset", "\\Controllers\\Auth\\ResetController->page");
        $system->route("GET|POST @auth_register: /register", "\\Controllers\\Auth\\RegisterController->page");

    }

}