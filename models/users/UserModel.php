<?php
namespace models\users;

use \models\AbstractModel;
use \models\DBfetchTrait;
use \system\Collection;
use \system\utilities\System;

class UserModel extends AbstractModel {
    use DBfetchTrait;

    protected $id = null;
    protected $name = null;
    protected $email = null;
    protected $password = null;
    protected $salt = null;
    protected $settings = null;

    protected $permissions = null;

    function __construct($DB = null) {
        parent::__construct($DB);
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

        $records = $this->fetch_data();
        foreach ($records as $record) {
            $this->set_from_array($record);
        }

        $profiler->stop();
        return $this;
    }

    function getAll() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $collection = new Collection();

        $records = $this->fetch_data();

        foreach ($records as $record) {
            $object = clone $this;
            $object->set_from_array($record);
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
        $records = $this->fetch_data();

        foreach ($records as $record) {
            $profiler->stop();
            return $record['c'];
        }

        $profiler->stop();
        return 0;
    }

    function save($allow_insert = true) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $table = new \DB\SQL\Mapper($this->DB, 'system_users');
        $table->load(["id = :ID", array(":ID" => $this->id)]);
        $fields = $table->fields();

        $changes = array();
        foreach (get_object_vars($this) as $key => $value) {
            if ($value === "") {
                $value = NULL;
            }
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if (isset($table->$key)) {
                //do shit
            }

            if (in_array($key, $fields)) {
                if ($table->$key != $value) {
                    $changes[$key] = array(
                        "w" => $table->$key,
                        "n" => $value,
                    );
                    $table->$key = $value;
                }
            }
        }
        $save = true;

        if ($table->dry() && !$allow_insert) {
            $save = false;
        }

        if ($save) {
            $table->save();
            $id = $table->_id;

            // return $this->get($id);
            $profiler->stop();
            return $this;
        }

        $profiler->stop();
    }
    function fetchPermissions() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $return = array();

        $records = $this->DB->exec("
            SELECT
                DISTINCT records.permission
            FROM (
                SELECT
                    `permission`
                FROM
                    `system_users_permissions`
                WHERE
                    user_id = :user_id

                UNION ALL

                SELECT
                    `permission`
                FROM
                    system_roles_permissions
                        INNER JOIN system_users_roles
                            ON system_users_roles.role_id = system_roles_permissions.role_id
                WHERE
                    system_users_roles.user_id = :user_id
            ) records
        ", array(
            ":user_id" => $this->getId(),
        ));

        $return = array_map(function ($item) {
            return $item['permission'];
        }, $records);

        $this->permissions = $return;

        $profiler->stop();
        return $this;
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
            $this->fetchPermissions();
        }

        foreach ($check_against as $perm) {
            if (!class_exists($perm)){
                $perm = "permissions." . $perm;
                $perm = str_replace(".", DIRECTORY_SEPARATOR, $perm);
            }

            $return[$perm] = false;

            if (in_array($perm, $this->permissions)) {
                $return[$perm] = true;
            } 
        }


        // System::debug($check_against,$return);
        $profiler->stop();
        if(in_array(false, $return, true) === false){
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
            $this->fetchPermissions();
        }

        foreach ($check_against as $perm) {
            if (!class_exists($perm)){
                $perm = "permissions." . $perm;
                $perm = str_replace(".", DIRECTORY_SEPARATOR, $perm);
            }

            if (in_array($perm, $this->permissions)) {
                $return = true;
            }
        }

        $profiler->stop();
        return $return;
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

    /**
     * Set the value of password
     *
     * @return  self
     */
    protected function set_password($password) { 
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
}