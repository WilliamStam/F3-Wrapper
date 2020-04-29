<?php
namespace controllers;

use models\users\CurrentUserModel;
use \models\users\UserModel;
use \system\Debug;
use \system\Output;
use \system\Profiler;
use \system\utilities\System;

class TestController extends AbstractController {
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
            $this->system->set("FORMAT", Output::JSON);
        }

        $data = array();

        // $user = new UserModel();
        // $user->id("1");
        // $user->name("new name");

      // System::Debug($this->system->get("USER"));



        // var_dump("-----------------------------------------------------------------");
        $users_get = (new UserModel())
            ->get("1")
            ->setSettings(date("Y-m-d H:i:s"))
            ->save(false)
        ;

        // System::debug($users_get);
        $users_getAll = (new UserModel())
            ->_where("ID != :ID", array(":ID" => 1000))
            ->_order("ID DESC")
            ->getAll()
        ;

        $t = array();

        foreach ($users_getAll as $item) {
            /** @var UserModel $item */
            $t[] = array(
                "name" => $item->getName(),
                "id" => $item->getId(),
            );
        }
    //    
        // System::debug($users_get->sql());
        // System::debug($users_get);
      //  System::debug($t);

      $this->render("home.twig", $data);
        $profiler->stop();
    }

}