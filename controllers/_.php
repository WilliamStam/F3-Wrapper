<?php
namespace controllers;
use \system\Assets;

class _ {
    function __construct($system) {
        $this->system = $system;

    }
    function beforeroute($system, $pattern, $handler) {

        // anything you want to run before the route happens for all controllers

        // Check CSRF stuff. can be either a post field or a header (incase we submit via ajax and we want to add it as an ajax global option)
        if ($system->get("VERB") == "POST") {
            $token = $this->system->get('POST.' . $this->system->get("CONFIG")['CSRF']);
            if (!$token) {
                $headers = apache_request_headers();
                $tokenKey = strtoupper('X-' . $this->system->get("CONFIG")['CSRF']);
                foreach ($headers as $key => $value) {
                    $key = strtoupper($key);
                    if ($key == $tokenKey) {
                        $token = $value;
                    }
                }
            }
            $csrf = $this->system->get('SESSION.CSRF');
            if (empty($token) || empty($csrf) || $token !== $csrf) {
                $this->system->error(403, "CSRF token doesnt match");
            }
        }

    }
    function afterroute($system) {

        // anything you want to run before the route happens for all controllers

    }
    static function routes($system) {

        foreach (array(
            \controllers\auth\_::class,
            \controllers\admin\_::class,
        ) as $router) {
            $router::routes($system);
        }

        $system->route("GET|POST|DELETE @index: /", "\\controllers\\TestController->page");

        $system->route("GET /test", function ($system) {
            $system->reroute('@test_controller(@FORMAT=html)');
        });
        $system->route("GET /test.@FORMAT", function ($system) {
            $system->reroute('@test_controller');
        });
        $system->route("GET|POST|DELETE @test_controller: /long/ass/folder/structure/testThisShit.@FORMAT", "\\controllers\\HomeController->page");

        $system->route("GET /php", function () {
            phpinfo();
            exit();
        });

        /* for testing purposes we set up some error routes */
        $system->route("GET|POST|DELETE /500", function ($system) {
            $t = strpos();
            $render = new Render("trying a 500 error.twig");
            $render->t = date("Y-m-d H:i:s");
            echo $render->render($system);
        });
        $system->route("GET|POST|DELETE /401", function ($system) {

            $system->error(401);
        });

/* Assets and media routes. only change if you know what you are doing */
        $system->route("GET " . $system->get("STATIC") . "/*", function ($system) {
            $asset = new Assets();
            $asset->setFolder($system->get("ASSETS"));
            $asset->setPath($system['PARAMS']['*']);
            $asset->render();
        });
        $system->route("GET /media/*", function ($system) {
            $asset = new Assets();
            $asset->setFolder($system->get("MEDIA"));
            $asset->setPath($system['PARAMS']['*']);
            $asset->render();
        });

    }

}