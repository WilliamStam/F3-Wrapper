<?php
namespace models\roles;

use \models\AbstractModel;
use \models\DBfetchTrait;
use \system\Collection;
use \system\db\Query;
use \system\db\Write;
use \system\utilities\System;

class RoleModel extends AbstractModel {
    use DBfetchTrait;

    protected $id = null;
    protected $role = null;
    protected $description = null;
    protected $category = null;
    protected $category_id = null;
    protected $permissions = array();

    function __construct($DB = null) {
        parent::__construct($DB);
        $this->schema(new RoleSchema());

        $this
            ->_select("system_roles.*, system_roles_categories.category")
            ->_from("
                system_roles
                    LEFT JOIN
                        system_roles_categories
                            ON system_roles_categories.id = system_roles.category_id
            ")
        ;
    }

    function fetch() {

        return $this->query()->fetch();
    }

    function get($id = null) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        if ($id != null) {
            $this->_where("system_roles.id = :id", array(":id" => $id));
        }
        $this->_limit("0,1");

        foreach ($this->fetch() as $record) {
            $this->setFromArray($record, true);
        }

        $profiler->stop();
        return $this;
    }

    function getAll() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $collection = new Collection();
        $collection->schema($this->schema());

        foreach ($this->fetch() as $record) {
            $object = clone $this;
            $object->setFromArray($record, true);
            $collection->add($object);
        }

        $profiler->stop();
        return $collection;
    }
    function getCount() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        $this
            ->_select("COUNT(system_roles.id) as c")
        ;
        $return = 0;
        foreach ($this->fetch() as $record) {
            $profiler->stop();
            $return = $record['c'];
        }

        $profiler->stop();
        return $return;
    }

    function save($allow_insert = true) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        foreach (get_object_vars($this) as $key => $value) {
            $values[$key] = $value;
        }

        $save = new Write('system_roles', $this->DB);
        $save->setWhere("id = :ID", array(":ID" => $this->id));
        $save->setSaveOnDry(true);
        $save->setAudit(true);
        $result = $save->save($values);
        $this->id = $result['id'];

        $existing_permissions = (array) $this->fetchPermissions();
        $new_permissions = (array) $this->permissions;

        // System::debug($existing_permissions,$new_permissions);
        foreach ($existing_permissions as $item){ // DELETE ROLES
            if ( !in_array($item, $new_permissions) ){
                $save = new Write('system_roles_permissions',$this->DB);
                $save->setWhere("permission = :permission AND role_id = :role_id",array(":permission" => $item,":role_id"=>$this->id));
                $save->delete();
            }
        }
        foreach ($new_permissions as $item){ // ADD ROLES
            if (!in_array($item,$existing_permissions)){
                $save = new Write('system_roles_permissions',$this->DB);
                $save->setWhere("permission = :permission AND role_id = :role_id",array(":permission" => $item,":role_id"=>$this->id));
                $save->save(array("permission" => $item,"role_id"=>$this->id));
            }
        }




        $profiler->stop();
        return $result;
    }
    function delete() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        $delete = new Write('system_roles', $this->DB);
        $delete->setWhere("id = :ID", array(":ID" => $this->id));
        $delete->setAudit(true);
        $result = $delete->delete();

        $profiler->stop();
    }

    function validate() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $errors = array();

        if (!$this->getRole()) {
            $errors['role'][] = "Role is required";
        }

        $profiler->stop();
        return $errors;
    }

    function fetchPermissions() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        $query = new Query();
        $query->setSelect("
            DISTINCT system_roles_permissions.permission
        ");
        $query->setFrom("
            system_roles_permissions
        ");
        $query->setWhere("
            system_roles_permissions.role_id = :role_id
        ", array(
            ":role_id" => $this->getId(),
        ));

        $records = $query->fetch();

        $return = array();
        foreach ($records as $item) {
            $return[] = $item['permission'];
        }

        $profiler->stop();
        return $return;
    }
    function getPermissions() {
        $return = false;
        if ($this->permissions == null && $this->getId()) {
            $this->permissions = $this->fetchPermissions();
        }
        return (array) $this->permissions;
    }

    /**
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of role
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * Set the value of role
     *
     * @return  self
     */
    public function setRole($role) {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of description
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of category
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * Set the value of category
     *
     * @return  self
     */
    public function setCategory($category) {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the value of category_id
     */
    public function getcategoryId() {
        return $this->category_id;
    }

    /**
     * Set the value of category_id
     *
     * @return  self
     */
    public function setcategoryId($category_id) {
        $this->category_id = $category_id;

        return $this;
    }

    

    /**
     * Set the value of permissions
     *
     * @return  self
     */
    public function setPermissions($permissions) {
        $this->permissions = $permissions;

        return $this;
    }
}