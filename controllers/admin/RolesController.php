<?php
namespace controllers\admin;
use \controllers\AbstractController;
use models\AbstractSchema;
use models\roles\RoleCategoryModel;
use models\SchemaInterface;
use models\roles\RoleModel;
use permissions\PermissionModel;
use \system\Debug;
use \system\Output;
use \system\Profiler;
use \system\utilities\Strings;
use \system\utilities\Arrays;
use \system\utilities\System;

class RolesController extends AbstractController {

    function beforeroute($system, $pattern, $handler) {
        parent::beforeroute($system, $pattern, $handler);


        //System::debug($this->system->get("ALIAS"));

        $permissions_check = $this->system->get("USER")->hasPermissions(array(
            \permissions\admin\Roles::class,
        ));
        if (!$permissions_check){
            $this->system->error(401,"burp");
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

        $this->render("admin\\roles\\page.twig", $data);
        $profiler->stop();
    }
    function _list($return){

        $where = "1";
        $params = array();

        if ($return['search']){
            $where .= " AND (system_roles.role LIKE :SEARCH or system_roles.description LIKE :SEARCH)";
            $params[':SEARCH'] = "%".$return['search']."%";
        }

        $list_data = (new RoleModel())
            ->_order("role ASC")
            ->_where($where,$params)
            ->getAll()
            ->toArray(new RoleListSchema())
        ;
        

        
        $return['list'] = $list_data;
        

        return $return;
    }
    function _details($return){
       
        $details = (new RoleModel())->get($return['id']);

        
        
    
        $return['details'] = $details->toArray();

       $role_categories = (new RoleCategoryModel())
            ->_order("category ASC")
            ->getAll()
            ->toArray()
        ;

    

        $return['categories'] = $role_categories;

        $permissions_list = (new PermissionModel())->getAll()->toArray();


        $role_permissions = $details->getPermissions();
        $permissions = array();

        foreach ($permissions_list as $item){
            $key = Strings::toAscii($item['group']);
            if (!isset($permissions[$key])){
                $permissions[$key] = array(
                    "label"=>$item['group'],
                    "items"=>array()
                );
            }

            $item['selected'] = in_array($item['id'], $role_permissions)?"1":"0";

            unset($item['group']);
            $permissions[$key]['items'][] = $item;
        }

        $return['permissions'] = $permissions;

        return $return;
    }

    function _save($return) {

        

        $details = (new RoleModel())->get($return['id']);

        $values = array(
            "role"=>$this->system->get("POST.role"),
            "description"=>$this->system->get("POST.description"),
            "category_id"=>$this->system->get("POST.category_id"),
            "permissions"=>(array)$this->system->get("POST.permissions"),
        );
        

        $details->setFromArray($values);
        
    
        // System::debug($details);

        $return['errors'] = $details->validate();

        if (empty( $return['errors'])){
            $id = $details->save();
            $return['id'] = $id;
        }
        
       

        return $return;
    }
    function _delete($return) {
        $details = (new RoleModel())->get($return['id']);

        $details->delete();
       

        return $return;
    }

    


}

class RoleListSchema extends AbstractSchema implements SchemaInterface {
    function toArray(){
        /**
         * @var RoleModel
         */
        $item = $this->item;
        
        return array(
            "id"=>$item->getId(),
            "role"=>$item->getRole(),
            "description"=>$item->getDescription(),
            "category_id"=>$item->getcategoryId(),
            "category"=>$item->getCategory(),
        );
    }
}