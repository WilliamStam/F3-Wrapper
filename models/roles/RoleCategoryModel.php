<?php
namespace models\roles;

use \models\AbstractModel;
use \models\DBfetchTrait;
use \system\Collection;
use \system\db\Write;
use \system\utilities\System;

class RoleCategoryModel extends AbstractModel {
    use DBfetchTrait;

    protected $id = null;
    protected $category = null;

    function __construct($DB = null) {
        parent::__construct($DB);
        $this->schema(new RoleCategorySchema());

        $this
            ->_select("system_roles_categories.*")
            ->_from("
                system_roles_categories
            ")
        ;
    }
    function fetch(){
        
        return $this->query()->fetch();
    }
    function get($id = null) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        if ($id != null) {
            $this->_where("system_roles_categories.id = :id", array(":id" => $id));
        }
        $this->_limit("0,1");



       

        foreach ($this->fetch() as $record) {
            $this->setFromArray($record, true);
        }

       

        $profiler->stop();
        return $this;
    }

    function getAll($apply_schema = null) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $collection = new Collection();
        $collection->schema($this->schema());

        

        foreach ($this->fetch() as $record) {
            $object = clone $this;
            $object->setFromArray($record, true);
            $collection->add($object);
        }
        // System::debug($collection);
        $profiler->stop();
        return $collection;
    }
    function getCount() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);

        $this
            ->_select("COUNT(system_roles_categories.id) as c")
        ;

        foreach ($this->fetch() as $record) {
            $profiler->stop();
            return $record['c'];
        }

        $profiler->stop();
        return 0;
    }

    function save($allow_insert = true) {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        
        foreach (get_object_vars($this) as $key => $value) {
            $values[$key] = $value;
        }

        $save = new Write('system_roles_categories',$this->DB);
        $save->setWhere("id = :ID",array(":ID" => $this->id));
        $save->setSaveOnDry(true);
        $save->setAudit(true);
        $result = $save->save($values);

        $profiler->stop();
        return $result;

    }
    function delete() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        
        $delete = new Write('system_roles_categories',$this->DB);
        $delete->setWhere("id = :ID",array(":ID" => $this->id));
        $delete->setAudit(true);
        $result = $delete->delete();
        
        $profiler->stop();
        return $result;
    }

    function validate() {
        $profiler = System::profiler(__CLASS__ . "::" . __FUNCTION__, __NAMESPACE__);
        $errors = array();

        if (!$this->getCategory()) {
            $errors['category'][] = "Category is required";
        }

        $profiler->stop();
        return $errors;
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
}