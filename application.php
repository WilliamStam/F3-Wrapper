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

$system->set('PROFILER', new Collection());
$system->set('PAGE_PROFILER', System::profiler("PAGE"));


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

    // your fresh page here:

    $error = $system->get("ERROR");

    $key = md5($error['text'] . "|" . $error['trace']);

    /* we want to do stuff on error. we also want to mask stuff if debug is off*/

    switch ((string) $error['code']) {
    case "404":
        $template = "404.twig";
        if (!$system->get('DEBUG')) {
            $error['text'] = "The page you seek seems to have wandered off...";
            $error['sub'] = "The page catches have been dispatched, but generally they seem to have a rather bad track record of finding missing pages. We keep them around because of Quota restrictions";
        }
        break;
    case "401":
        $template = "401.twig";
        if (!$system->get('DEBUG')) {
            $error['text'] = "Not allowed there";
        }
        break;
    case "403":
        $template = "403.twig";
        break;
    case "500":

        $template = "500.twig";
        if (!$system->get('DEBUG')) {
            try {
                /* lets log it to the DB */
                $system->get("DB")->exec("
                        INSERT INTO system_errors (
                            `error_key`, `datetime_added`, `datetime_last`, `version`, `code`, `url`, `count`, `message`, `trace`
                        ) VALUES (
                            :KEY,now(),now(),:VERSION, :CODE, :URL, 1, :MESSAGE, :TRACE
                        ) ON DUPLICATE KEY UPDATE
                            `datetime_last` = VALUES(datetime_last),
                            `count` = VALUES(count) + 1
                    ", array(
                    ":KEY" => $key,
                    ":VERSION" => $system->get("VERSION"),
                    ":CODE" => $error['code'],
                    ":URL" => $system->get("URI"),
                    ":MESSAGE" => $error['text'],
                    ":TRACE" => $error['trace'],
                ));

            } catch (\Throwable $e) {
                // we cant exactly let the writing to db throw an error on an error...
            }

            $error['status'] = "Internal Error";
            $error['code'] = "500";
            $error['text'] = "We hit a bridge at high speed";
            $error['sub'] = "A team of highly trained forensic hamsters have been dispatched to scour through the wreckage and attempt to make sense of the situation.";
        }

        break;
    default:
        $template = "error.twig";
        break;
    }

    $render = new Template("_errors/" . $template);
    $render->code = $error['code'];
    $render->status = $error['status'];
    $render->text = $error['text'];
    $render->sub = $error['sub'];

    $output = new Output();
    $output->setBody($render->render());
    $output->setData($error);

    $system->get('PAGE_PROFILER')->stop();
    // need to force the output of the error here

    $output->setProfiler($system->get("PROFILER"));
    $output->output();

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




$user = false;
if ($system->get("SESSION.user_id")){
    $profiler = System::profiler("Find current user");
    $user = ( new Models\UserModel() )->get($system->get("SESSION.user_id"));
    $profiler->stop();
}




// System::debug($system->get("SESSION.user_id"),$user);

$system->set("USER",(new CurrentUserModel())->get($system->get("SID")));





$system->set('VARIABLES', array(
    "ASSETS" => $system->get("STATIC"),
    "PACKAGE" => $system->get("PACKAGE"),
    "VERSION" => $system->get("VERSION"),
    "CSRF_NAME" => $system->get("CONFIG")['CSRF'],
    "CSRF_TOKEN" => $system->get("SESSION.CSRF"),
    

));


Controllers\_::routes($system);




$system->run();
// $system->get("RESPONSE")->render();
$system->get('PAGE_PROFILER')->stop();

$output = new Output();
$output->setBody($system->get("BODY"));
$output->setData($system->get("DATA"));
$output->setProfiler($system->get("PROFILER"));
$output->output();
// var_dump($output);