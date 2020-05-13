<?php
namespace controllers\admin;
use models\AbstractSchema;
use models\SchemaInterface;
use models\users\UserModel;
use permissions\PermissionsList;
use \controllers\AbstractController;
use models\roles\RoleModel;
use models\users\UserValidate;
use \system\Debug;
use \system\Profiler;
use \system\utilities\Strings;
use \system\utilities\System;

class UsersController extends AbstractController {

    function beforeroute($system, $pattern, $handler) {
        parent::beforeroute($system, $pattern, $handler);

        

        $permissions_check = $this->system->get("USER")->hasPermissions(array(
            \permissions\admin\Users::class,
        ));


        // System::debug($this->system->get("USER"));
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
        $data["errors"] = array();
        $data["search"] = $this->system->get("REQUEST.search");


      

        switch ($this->system->get("VERB")) {
        case "POST":
            $data = $this->_save($data);
            break;
        case "DELETE":
            $data = $this->_delete($data);
            break;
        default:
            if ($data['id']) {
                $data = $this->_details($data);
            } else {
                $data = $this->_list($data);
            }
            break;
        }

        $this->render("admin\\users\\page.twig", $data);
        $profiler->stop();
    }
    function _list($return) {

        $where = "1";
        $params = array();

        if ($return['search']) {
            $where .= " AND (system_users.name LIKE :SEARCH or email LIKE :SEARCH)";
            $params[':SEARCH'] = "%" . $return['search'] . "%";
        }

        $list_data = (new UserModel())
            ->_order("name ASC")
            ->_where($where, $params)
            ->getAll()
        ;

        $list = array();
        foreach ($list_data as $item){
            $list[] = $item->schema()->toArray();
        }
        

        $return['list'] = $list;

        return $return;
    }
    function _details($return) {

        $details = (new UserModel())->get($return['id'])->schema()->toArray();

        $return['details'] = $details;

        //$permissions = (new PermissionsList())->list();

        //System::debug($permissions);

        


        $userRolesId = array_map(function($item){
            return $item;
        },$details['_']->getRoles());


        $all_roles = (new RoleModel())
            ->_order("role ASC")
            ->getAll()
        ;
        $list = array();
        foreach ($all_roles as $item){
            $list[] = $item->schema((new RolesListSchema())->addUserRolesIds($userRolesId))->toArray();
        }

        
        // 
        $roles_data = array();
        foreach ($list as $item){
            if (!isset($roles_data[$item['category_id']])){
                $roles_data[$item['category_id']] = array(
                    "id"=>$item['category_id'],
                    "label"=>$item['category'],
                    "items"=>array()
                );
            }

            $roles_data[$item['category_id']]['items'][] = array(
                "id"=>$item['id'],
                "name"=>$item['name'],
                "description"=>$item['description'],
                "selected"=>$item['selected'],
            );
        }
        


        
            
        
            // System::debug($roles_data);

        $return['roles'] = $roles_data;
        

        return $return;
    }

    function _save($return) {

        $values = array(
            "name"=>$this->system->get("POST.name"),
            "email"=>$this->system->get("POST.email"),
            "roles"=>$this->system->get("POST.roles"),
            
        );
        // $values = array(
        //     "name"=>"fish",
        //     "email"=>"cake@test.com",
        //     "roles"=>array(
        //         "1",
        //         "3"
        //     )
        // );

        // System::debug($this->system->get("POST.roles"));

        $details = (new UserModel())->get($return['id']);
        if ($this->system->get("POST.password")) {
            $values['password'] = $this->system->get("POST.password");
        }
        $details->setFromArray($values);
        
        $return['errors'] = $details->validate();



        


        if (empty( $return['errors'])){
            $id = $details->save();
            $return['id'] = $id;
        }
        
       

        return $return;
    }
    function _delete($return) {
        $details = (new UserModel())->get($return['id']);
        $details->delete();
        return $return;
    }

}

class RolesListSchema extends AbstractSchema implements SchemaInterface {
    protected $user_roles_ids = array();
    function addUserRolesIds($user_roles_ids){
        $this->user_roles_ids = $user_roles_ids;

        return $this;
    }
    function toArray() {
        /**
         * @var RoleModel
         */
        $item = $this->item;

        $selected = "0";

        if (in_array($item->getId(),$this->user_roles_ids)){
            $selected = "1";
        }

        return array(
            "id" => $item->getId(),
            "name" => $item->getRole(),
            "description" => $item->getDescription(),
            "category_id" => $item->getCategoryId(),
            "category" => $item->getCategory(),
            "selected"=>$selected
        );
    }
}