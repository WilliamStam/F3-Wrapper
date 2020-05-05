<?php
declare (strict_types = 1);
namespace system;

use system\utilities\Arrays;
use system\utilities\Strings;
use system\utilities\System;

class Template {

    protected $TEMPLATE;
    protected $DATA;
    protected $ASSETS;

    function __construct($template = "") {
        $this->TEMPLATE = $template;
    }

    public function __set($property, $value) {
        $this->DATA[$property] = $value;
    }

    public function __get($property) {
        return $this->DATA[$property];
    }

    function render() {
        $system = \Base::instance();
        $folder = $system->get("TEMPLATES");

        $profiler = $system->get("PROFILER")->add(new Profiler("[Templates]" . DIRECTORY_SEPARATOR . $this->TEMPLATE, "TWIG"));

        $loader = new \Twig\Loader\FilesystemLoader($folder);

        $twig = new \Twig\Environment($loader, [
            'debug' => $system->get("DEBUG"),
            'cache' => $system->get("CACHE") ? $system->get("TEMP") . DIRECTORY_SEPARATOR . "cache" : false,
        ]);
        $filter = new \Twig\TwigFilter('var_dump', function (\Twig\Environment $env, $string) {
            return var_dump($string);
        }, ['needs_environment' => true]);
        $twig->addFilter($filter);
        
        $filter = new \Twig\TwigFilter('get', function (\Twig\Environment $env, $string) use ($system) {
            return $system->get($string);
        }, ['needs_environment' => true]);
        $twig->addFilter($filter);

        $filter = new \Twig\TwigFilter('route', function (\Twig\Environment $env, $string, $options=false) use ($system) {

            $aliases = $system->get("ALIASES");

            $params = $system->get("PARAMS");
            foreach ($system->split($options) as $item) {
                $parts = explode('=', $item, 2);
                $params[$parts[0]] = $parts[1];
            }

            $route = $system->build($aliases[$string], $params);
            return $route;
        }, ['needs_environment' => true]);
        $twig->addFilter($filter);

        $twigData = $this->DATA;

        foreach ($system->get("VARIABLES") as $k=>$v){
            // $k = substr($k);
            $twigData[$k] = $v;
        };
        $twigData['DEBUG'] = $system->get("DEBUG");
        $twigData['USER'] = $system->get("USER");
        $twigData['SYSTEM'] = $system;


        $body = $twig->render($this->TEMPLATE, $twigData);

        $profiler->stop();
        return $body;
    }

}