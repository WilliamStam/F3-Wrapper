<?php
namespace controllers\admin;
use models\AbstractSchema;
use models\SchemaInterface;
use models\users\UserModel;
use permissions\PermissionsList;
use \controllers\AbstractController;
use models\roles\RoleModel;
use models\users\UserValidate;
use system\db\Query;
use \system\Debug;
use \system\Profiler;
use \system\utilities\Strings;
use \system\utilities\System;

class StatusController extends AbstractController {

    function beforeroute($system, $pattern, $handler) {
        parent::beforeroute($system, $pattern, $handler);

        $permissions_check = $this->system->get("USER")->hasPermissions(array(
            \permissions\admin\SystemStatus::class,
        ));
        if (!$permissions_check) {
            $this->system->error(401);
        }

    }
    function afterroute($system) {

        parent::afterroute($system);
    }
    function page() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $data = array();

        $data["id"] = $_GET['id'] ?? false;
        
        $data["errors"] = (new Query())
            ->setSelect("*")
            ->setFrom("system_errors")
            ->setWhere("version = :version",array(":version"=>$this->system->get("VERSION")))
            ->setOrder("datetime_last DESC")
            ->fetch()

        ;

    //    System::debug($data['errors']);


      

        $this->render("admin\\status\\page.twig", $data);
        $profiler->stop();
    }

}
