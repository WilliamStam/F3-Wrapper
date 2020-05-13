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

        $system->route("GET|POST|DELETE @admin_roles: /admin/roles", "\\controllers\\admin\\RolesController->page");
        $system->route("GET|POST|DELETE @admin_roles_categories: /admin/roles/categories", "\\controllers\\admin\\RolesCategoriesController->page");
        $system->route("GET|POST|DELETE @admin_users: /admin/users", "\\controllers\\admin\\UsersController->page");
        $system->route("GET|POST|DELETE @admin_system_status: /admin/status", "\\controllers\\admin\\StatusController->page");

    }

}