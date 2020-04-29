<?php
namespace models\users;

use \system\Collection;
use \system\utilities\System;

use \models\AbstractModel;
use \models\DBfetchTrait;


class UserModel extends AbstractModel {
    use DBfetchTrait;

    protected $id = null;
    protected $name = null;
    protected $email = null;
    protected $password = null;
    protected $salt = null;
    protected $settings = null;

    function __construct($DB = null) {
        parent::__construct($DB);
        $this
            ->_select("users.*")
            ->_from("users")
        ;
    }

    function user_key(){
        return $this->getId() . "|" . $this->getSalt();
    }


    function get($id = null) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        if ($id != null) {
            $this->_where("users.id = :id", array(":id" => $id));
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


    function save($allow_insert=true){
        $table=new \DB\SQL\Mapper($this->DB,'users');
        $table->load(["id = :ID",array(":ID"=>$this->id)]);
        $fields = $table->fields();

        $changes = array();
        foreach (get_object_vars($this) as $key=>$value){
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
        
        if ($table->dry() && !$allow_insert){
            $save = false;
        }

        if ($save){
            $table->save();
            $id = $table->_id;

            // return $this->get($id);
            return $this;
        }



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
        $this->password = password_hash($password,PASSWORD_DEFAULT);
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
    public function getSalt()
    {
        return $this->salt;
    }
}