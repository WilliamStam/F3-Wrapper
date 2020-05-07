<?php
namespace controllers\admin;
use \controllers\AbstractController;
use \system\Debug;
use \system\Output;
use \system\Profiler;
use \system\utilities\System;

class RolesController extends AbstractController {
    
    function beforeroute($system, $pattern, $handler) {
        parent::beforeroute($system, $pattern, $handler);


        //System::debug($this->system->get("ALIAS"));

        $permissions_check = $this->system->get("USER")->hasPermissions(array(
            \permissions\admin\Roles::class,
            \permissions\admin\Roles2::class,
            \permissions\admin\Roles3::class,
        ));
        if (!$permissions_check){
            $this->system->error(401, "Sorry you dont have authorization to view this awesome");
        }
    }
    function afterroute($system) {

        
        parent::afterroute($system);
    }
    function page() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $data = array();




        $this->render("admin\\roles.twig", $data);
        $profiler->stop();
    }

}