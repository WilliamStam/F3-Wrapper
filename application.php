<?php
declare (strict_types = 1);

use models\users\CurrentUserModel;
use system\Assets;
use system\Collection;
use system\Output;
use system\Profiler;
use system\Render;
use system\Template;
use system\utilities\Arrays;
use system\utilities\Strings;
use system\utilities\System;

spl_autoload_register(function ($class) {
    $root = (__DIR__);

    $path = $root . DIRECTORY_SEPARATOR . $class;
    $path = str_replace(array("\\", "/", "//", "\\\\"), DIRECTORY_SEPARATOR, $path);
    $path = $path . ".php";

       
    if (file_exists($path)) {
        include ($path);
    } else {
        return FALSE;
    }
});

require 'vendor/autoload.php';

$system = \Base::instance();

$config = array();
$configFile = __DIR__.DIRECTORY_SEPARATOR."config.php";
if (file_exists($configFile)) {
    $config = require_once $configFile;
}
$defaultConfig = require_once "config.default.php";


$system->set("CONFIG", Arrays::replace($defaultConfig, $config));


$system->set("ROOT", realpath(__DIR__ ));
$system->set("SEED", $system->get("CONFIG")['SEED']);

$system->set('PACKAGE', "Application");
$system->set('VERSION', "v1");

$system->set("CACHE", $system->get("CONFIG")['CACHE']);
$system->set("DEBUG", $system->get("CONFIG")['DEBUG']);

// System::debug($system);

// what status messages to log to file on error (ignoring 404)
// $system->set("LOGGABLE", "500");

$system->set("TEMP", $system->get("CONFIG")['TEMP']);
$system->set("UPLOADS", $system->get("CONFIG")['TEMP']);
$system->set("LOGS", $system->get("CONFIG")['LOGS']);
$system->set("MEDIA", $system->get("CONFIG")['MEDIA']);

$system->set("TZ", $system->get("CONFIG")['TZ']);
$system->set("TAGS", $system->get("CONFIG")['TAGS']);

$system->set("ERRORFILE", Strings::fixDirSlashes($system->get("LOGS") . "/" . Strings::toAscii(date('Y-m ') . "-" . $system->get("VERSION"), "_") . ".log"));

$system->set('DEBUG', $system->get("CONFIG")['DEBUG']);
$system->set('DATA', array());
$system->set('QUIET', true);
$system->set('HALT', true);

$system->set('PROFILER', new Collection());
$system->set('OUTPUT', new Output());

$page_profiler_string = "[".$system->get("VERB")."] ".$system->get("URI");
if ($system->ajax()){
    $page_profiler_string = "XHR - ". $page_profiler_string;
}
$system->set('PAGE_PROFILER', System::profiler($page_profiler_string));


$system->set("ASSETS", Strings::fixDirSlashes($system->get("ROOT") . "/assets"));
$system->set("UI", $system->get("ASSETS"));
$system->set("TEMPLATES", Strings::fixDirSlashes($system->get("ASSETS") . "/templates"));

$system->set("STATIC", "/static/" . $system->get("VERSION"));

$system->set("FORMAT", Output::AUTO);

// var_dump($system->get("CONFIG"));
// exit();
if (!file_exists(dirname($system->get("ERRORFILE")))) {
    mkdir(dirname($system->get("ERRORFILE")), 01777, TRUE);
}

ini_set('error_log', $system->get("ERRORFILE"));

// since the DB could slow stuff down lets find out by how much....
$profiler = System::profiler("DB connection");



$system->set("DB", new \DB\SQL('mysql:host=' . $config['DB']['HOST'] . ":" . $config['DB']['PORT'] . ';dbname=' . $config['DB']['DATABASE'], $config['DB']['USERNAME'], $config['DB']['PASSWORD'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)));

$profiler->stop();


$profiler = System::profiler("Session Handler");
$session = new \DB\SQL\Session($system->get("DB"), 'system_sessions', FALSE, function () {return true;});



$system->set("SID",$system->get('COOKIE.PHPSESSID'));


if (!$system->get("SESSION.CSRF")) {
   $system->set("SESSION.CSRF", $session->csrf());
}
$profiler->stop();


$system->set("ONERROR", function ($system) {
    // recursively clear existing output buffers:

    while (ob_get_level()) {
        ob_end_clean();
    }

    $error = $system->get("ERROR");

    $error_object = "\\system\\errors\\Error".$error['code'];
    if (class_exists(($error_object))){
        $error_object = new $error_object();
    } else {
        $error_object = new \system\errors\ErrorGeneric();
    }
    $error_object->output();



});

if ($system->get("DEBUG")) {
    $whoops = new \Whoops\Run;
    if (false) {
        $handler = new \Whoops\Handler\JsonResponseHandler();
    } else {
        $handler = new \Whoops\Handler\PrettyPageHandler();
        $handler->setEditor(function ($file, $line) {
            return "editor:$file:$line";
        });
    }
    $whoops->pushHandler($handler);
    $whoops->register();
}





$system->set("USER",(new CurrentUserModel())->get($system->get("SID")));
$system->get("USER")->setLastActive(Date("Y-m-d H:i:s"));




$system->set('VARIABLES', array(
    "ASSETS" => $system->get("STATIC"),
    "PACKAGE" => $system->get("PACKAGE"),
    "VERSION" => $system->get("VERSION"),
    "CSRF_NAME" => $system->get("CONFIG")['CSRF'],
    "CSRF_TOKEN" => $system->get("SESSION.CSRF"),
    

));


Controllers\_::routes($system);


$system->run();
try {

} catch (\Throwable $w){
    //System::debug($w);
}


$system->get("USER")->save();

// $system->get("RESPONSE")->render();
$system->get('PAGE_PROFILER')->stop();


$system->get("OUTPUT")->setProfiler($system->get("PROFILER"));
$system->get("OUTPUT")->output();
