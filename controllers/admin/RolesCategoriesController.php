<?php
namespace controllers\admin;
use \controllers\AbstractController;
use models\AbstractSchema;
use models\SchemaInterface;
use models\roles\RoleCategoryModel;
use \system\Debug;
use \system\Output;
use \system\Profiler;
use \system\utilities\Strings;
use \system\utilities\Arrays;
use \system\utilities\System;

class RolesCategoriesController extends AbstractController {

    function beforeroute($system, $pattern, $handler) {
        parent::beforeroute($system, $pattern, $handler);


        //System::debug($this->system->get("ALIAS"));

        $permissions_check = $this->system->get("USER")->hasPermissions(array(
            \permissions\admin\RolesCategories::class,
        ));
        if (!$permissions_check){
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


        $this->render("admin\\roles_categories\\page.twig", $data);
        $profiler->stop();
    }
    function _list($return){

        $where = "1";
        $params = array();

        if ($return['search']){
            $where .= " AND (system_roles_categories.categoru LIKE :SEARCH )";
            $params[':SEARCH'] = "%".$return['search']."%";
        }

        $list = (new RoleCategoryModel())
            ->_order("category ASC")
            ->_where($where,$params)
            ->getAll()
            ->toArray()
        ;
        

        


        
        $return['list'] = $list;
        

        return $return;
    }
    function _details($return){
       
        $details = (new RoleCategoryModel())->get($return['id'])->toArray();
    
        $return['details'] = $details;

       



        return $return;
    }

    function _save($return) {

        

        $details = (new RoleCategoryModel())->get($return['id']);

        $values = array(
            "category"=>$this->system->get("POST.category"),
        );
        

        $details->setFromArray($values);
        
    
        $return['errors'] = $details->validate();

        if (empty( $return['errors'])){
            $id = $details->save();
            $return['id'] = $id;
        }
        
       

        return $return;
    }
    function _delete($return) {
        $details = (new RoleCategoryModel())->get($return['id']);
        $details->delete();
        return $return;
    }

    


}

class RoleCategoryListSchema extends AbstractSchema implements SchemaInterface {
    function toArray(){
        /**
         * @var RoleCategoryModel
         */
        $item = $this->item;
        
        return array(
            "id"=>$item->getId(),
            "category"=>$item->getCategory(),
        );
    }
}