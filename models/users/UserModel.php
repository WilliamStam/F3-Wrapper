<?php
namespace models\users;

use models\SchemaInterface;
use \models\AbstractModel;
use \models\DBfetchTrait;
use \models\DBsaveTrait;
use \system\Collection;
use \system\utilities\System;
use \system\utilities\Arrays;
use \system\db\Query;
use \system\db\Write;

class UserModel extends AbstractModel {
    use DBfetchTrait;


    protected $id = null;
    protected $name = null;
    protected $email = null;
    protected $password = null;
    protected $salt = null;
    protected $settings = null;
    protected $last_active = null;

    protected $permissions = null;
    protected $roles = null;

    protected $errors = array();


    function __construct($DB = null) {
        parent::__construct($DB);
        $this
            ->schema(new UserSchema())
        ;

        $this
            ->_select("system_users.*")
            ->_from("system_users")
        ;
    }


    function userKey() {
        return $this->getId() . "|" . $this->getSalt();
    }


    
    function get($id = null) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        if ($id != null) {
            $this->_where("system_users.id = :id", array(":id" => $id));
        }
        $this->_limit("0,1");
        
        foreach ($this->fetch() as $record) {
            $this->setFromArray($record,true);
        }

        $profiler->stop();
        return $this;
    }

    function fetch(){
        return $this->query()->fetch();
    }
   
    function getAll() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $collection = new Collection();
        $collection->schema($this->schema());

        foreach ($this->fetch() as $record) {
            $object = clone $this;
            $object->setFromArray($record,true);
            $collection->add($object);
        }
        $profiler->stop();
        return $collection;
    }
    function getCount() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        $this
            ->_select("COUNT(system_users.id) as c")
            
        ;
        $return = 0;
        // System::debug($records);
        foreach ($this->fetch() as $record) {
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

        $save = new Write('system_users',$this->DB);
        $save->setWhere("id = :ID",array(":ID" => $this->id));
        $save->setSaveOnDry(true);
        $save->setAudit(true);
        $result = $save->save($values);

        $this->id = $result['id'];


        $existing_roles = (array) $this->fetchRoles();
        $new_roles = (array) $this->roles;

        foreach ($existing_roles as $item){ // DELETE ROLES
            if ( !in_array($item, $new_roles) ){
                $save = new Write('system_users_roles',$this->DB);
                $save->setWhere("user_id = :user_id AND role_id = :role_id",array(":user_id" => $this->id,":role_id"=>$item));
                $save->delete();
            }
        }
        
        

        foreach ($new_roles as $item){ // ADD ROLES
            if (!in_array($item,$existing_roles)){
                $save = new Write('system_users_roles',$this->DB);
                $save->setWhere("user_id = :user_id AND role_id = :role_id",array(":user_id" => $this->id,":role_id"=>$item));
                $save->save(array("user_id"=>$this->id,"role_id"=>$item));
            }
        }

        

        // System::debug($existing_roles,$this->roles);
        $profiler->stop();
        return $result;
    }
    function delete(){
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        
        $delete = new Write('system_users',$this->DB);
        $delete->setWhere("id = :ID",array(":ID" => $this->id));
        $delete->setAudit(true);
        $result = $delete->delete();
        
        foreach ((array)$this->fetchRoles() as $item){
            $save = new Write('system_users_roles',$this->DB);
            $save->setWhere("user_id = :user_id AND role_id = :role_id",array(":user_id" => $this->id,":role_id"=>$item));
            $save->delete();
        }
        


        $profiler->stop();
    }

    function validate() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $errors = array();

        if (!$this->getName()) {
            $errors['name'][] = "Name is required";
        }
        if ($this->getEmail()) {
            if (filter_var($this->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $email_in_use = (new UserModel())
                    ->_where("LOWER(email) = :email AND id != :id", array(
                        ":email" => strtolower($this->getEmail()),
                        ":id" => (string)$this->getId(),
                    ))
                    ->getCount();

                // System::debug($this->system->get("DB")->log());
                if ($email_in_use != "0") {
                    $errors['email'][] = "The email address is already in use";
                }
            } else {
                $errors['email'][] = "Invalid email format";
            }

        } else {
            $errors['email'][] = "e-mail is required";
        }


        $profiler->stop();
        return $errors;
    }

    function fetchPermissions() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $return = array();

        $records = $this->DB->exec("
            SELECT
                records.permission, 
                records.source 
            FROM (
                SELECT
                    `permission`,'user' as source
                FROM
                    `system_users_permissions`
                WHERE
                    user_id = :user_id

                UNION ALL

                SELECT
                    `permission`, 'role' as source
                FROM
                    system_roles_permissions
                        INNER JOIN system_users_roles
                            ON system_users_roles.role_id = system_roles_permissions.role_id
                        INNER JOIN system_roles
                            ON system_users_roles.role_id = system_roles.id
                WHERE
                    system_users_roles.user_id = :user_id AND system_roles.id IS NOT null
            ) records
        ", array(
            ":user_id" => $this->getId(),
        ));

        $return = array(
        );
        foreach ($records as $item){
            $return[$item['source']][] = $item;
        }
       
        $profiler->stop();
        return $return;
    }
    /**
     * Check if the user has ALL the passed in permissions (by either role or assigned directly)
     * @param array/string $check_against: either an array or a single string
     * can be in either full class path or dot notation ie:
     *   permissions/some/Permission - mapped to the permission class
     * OR
     *   some.Permission - adds in the permissions/some/Permission
     *
     * all permissions must be true for it to return true
     *
     * @return bool
     */
    function hasPermissions($check_against = array()) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $return = array();

        if (is_string($check_against)) {
            $check_against = array($check_against);
        }
        
        if ($this->permissions == null && $this->getId()) {
            $this->permissions = $this->fetchPermissions();
        }

        $permissions = array();
        foreach ((array)$this->permissions as $source){
            foreach ($source as $item){
                if (!in_array($item['permission'],$permissions)){
                    $permissions[] = $item['permission'];
                }
            }
        }

        foreach ($check_against as $perm) {
            if (!class_exists($perm)) {
                $perm = "permissions." . $perm;
                $perm = str_replace(".", "\\", $perm);
            }

            $return[$perm] = false;

            if (in_array($perm, $permissions)) {
                $return[$perm] = true;
            }
        }

        $profiler->stop();
        if (in_array(false, $return, true) === false) {
            return true;
        }
        return false;
    }
    /**
     * Check if the user has SOME of the passed in permissions (by either role or assigned directly)
     * @param array/string $check_against: either an array or a single string
     * can be in either full class path or dot notation ie:
     *   permissions/some/Permission - mapped to the permission class
     * OR
     *   some.Permission - adds in the permissions/some/Permission
     *
     * if any single permissionr eturns true, this returns true. (usefull if you want to show an admin menu for instance)
     *
     * @return bool
     */
    function hasSomePermissions($check_against = array()) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $return = false;

        if (is_string($check_against)) {
            $check_against = array($check_against);
        }

        if ($this->permissions == null && $this->getId()) {
            $this->permissions = $this->fetchPermissions();
        }

        $permissions = array();
        foreach ((array)$this->permissions as $source){
            foreach ($source as $item){
                if (!in_array($item['permission'],$permissions)){
                    $permissions[] = $item['permission'];
                }
            }
        }

        foreach ($check_against as $perm) {
            if (!class_exists($perm)) {
                $perm = "permissions." . $perm;
                $perm = str_replace(".", DIRECTORY_SEPARATOR, $perm);
            }

            if (in_array($perm, $permissions)) {
                $return = true;
            }
        }

        $profiler->stop();
        return $return;
    }

    function fetchRoles() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);


        $query = new Query();
        $query->setSelect("
            DISTINCT system_roles.id
        ");
        $query->setFrom("
            system_roles
                INNER JOIN
                    system_users_roles ON system_users_roles.role_id = system_roles.id
        ");
        $query->setWhere(" 
            system_users_roles.user_id = :user_id
        ", array(
            ":user_id" => $this->getId(),
        ));

        $records = $query->fetch();

        $return = array();
        foreach ($records as $item){
            $return[] = $item['id'];
        }


        $profiler->stop();
        return $return;
    }
    function getRoles() {
        $return = false;
        if ($this->roles == null && $this->getId()) {
            $this->roles = $this->fetchRoles();
        }
        return (array) $this->roles;
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
     * Get the value of name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword() {
        return $this->password;
    }

    protected function rawSetPassword($password) {
        $this->password = $password;
        return $this;
    }
    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->salt = uniqid('', true);

        return $this;
    }

    /**
     * Get the value of settings
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * Set the value of settings
     *
     * @return  self
     */
    public function setSettings($settings) {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get the value of salt
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * Get the value of last_active
     */
    public function getLastActive() {
        return $this->last_active;
    }

    /**
     * Set the value of last_active
     *
     * @return  self
     */
    public function setLastActive($last_active) {
        $this->last_active = $last_active;

        return $this;
    }
}