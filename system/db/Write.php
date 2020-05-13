<?php
namespace system\db;

class Write {
    protected $DB = null;
    protected $TABLE = null;
    protected $WHERE = null;
    protected $PARAMS = array();
    protected $VALUES = array();
    protected $SAVE_ON_DRY = true;
    protected $RETURN_PK = "id";
    protected $AUDIT = true;

    function __construct($table = null, $DB_connection = null) {
        $this->system = \Base::instance();
        if ($table) {
            $this->setTable($table);
        }
        $this->DB = $DB_connection;
        if (!$this->DB) {
            $this->DB = $this->system->get("DB");
        }

    }
    function delete(){
        $table = new \DB\SQL\Mapper($this->DB, $this->getTable());
        $table->load([$this->getWhere(), $this->getParams()]);
        $fields = $table->fields();



        $pk = $this->getReturnPK();
        $before_action_pk = $table->$pk;

        $table_values = array();
        foreach ($fields as $field){
            $table_values[$field] = $table->$field;
        }
        
        // System::debug($table);
        
        $table->erase();    

        if ($this->getAudit()) {
            $this->DB->exec("
                INSERT INTO system_audit (
                    action,
                    user_id,
                    pk,
                    source,
                    data,
                    datetime
                ) VALUES (
                    :action,
                    :user_id,
                    :pk,
                    :source,
                    :data,
                    now()
                )",array(
                    ":action"=>"delete",
                    ":user_id"=>$this->system->get("USER")->getId(),
                    ":pk"=>$before_action_pk,
                    ":source"=>$this->getTable(),
                    ":data"=>json_encode($table_values),
                ));
        }


        return true;
    }

    function save($values = null) {
        if ($values) {
            $this->setValues($values);
        }

        $table = new \DB\SQL\Mapper($this->DB, $this->getTable());
        $table->load([$this->getWhere(), $this->getParams()]);
        $fields = $table->fields();

        $changes = array();
        foreach ($this->getValues() as $key => $value) {
            if ($value === "") {
                $value = NULL;
            }
            if (is_array($value)) {
                $value = json_encode($value);
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

        if ($table->dry() && !$this->getSaveOnDry()) {
            $save = false;
        }
        $return = false;
        if ($save) { 
            $pk = $this->getReturnPK();
            $before_save_pk = $table->$pk;

            $table->save();
           
            $id = $table->$pk;

            $return = array();
            $return[$pk] = $id;
            $return['changes'] = $changes;

            $action = $before_save_pk != $return[$pk] ? "insert": "update";
       

            if ($this->getAudit() && count($changes)) {
                $this->DB->exec("
                    INSERT INTO system_audit (
                        action,
                        user_id,
                        pk,
                        source,
                        data,
                        datetime
                    ) VALUES (
                        :action,
                        :user_id,
                        :pk,
                        :source,
                        :data,
                        now()
                    )",array(
                        ":action"=>$action,
                        ":user_id"=>$this->system->get("USER")->getId(),
                        ":pk"=>$return[$pk],
                        ":source"=>$this->getTable(),
                        ":data"=>json_encode($changes),
                    ));
            }
        }

        return $return;

    }


    /**
     * Get the value of TABLE
     */
    public function getTable() {
        return $this->TABLE;
    }

    /**
     * Set the value of TABLE
     *
     * @return  self
     */
    public function setTable($table) {
        $this->TABLE = $table;

        return $this;
    }

    /**
     * Get the value of WHERE
     */
    public function getWhere() {
        return $this->WHERE;
    }

    /**
     * Set the value of WHERE
     *
     * @return  self
     */
    public function setWhere($where, $params = array()) {
        $this->WHERE = $where;
        $this->setParams($params);

        return $this;
    }

    /**
     * Get the value of PARAMS
     */
    public function getParams() {
        return $this->PARAMS;
    }

    /**
     * Set the value of PARAMS
     *
     * @return  self
     */
    public function setParams($params) {
        $this->PARAMS = $params;

        return $this;
    }

    /**
     * Get the value of VALUES
     */
    public function getValues() {
        return $this->VALUES;
    }

    /**
     * Set the value of VALUES
     *
     * @return  self
     */
    public function setValues($values) {
        $this->VALUES = $values;

        return $this;
    }

    /**
     * Get the value of SAVE_ON_DRY
     */
    public function getSaveOnDry(): bool {
        return $this->SAVE_ON_DRY;
    }

    /**
     * Set the value of SAVE_ON_DRY
     *
     * @return  self
     */
    public function setSaveOnDry($save_on_dry) {
        $this->SAVE_ON_DRY = $save_on_dry;

        return $this;
    }

    /**
     * Get the value of RETURN_PK
     */
    public function getReturnPK() {
        return $this->RETURN_PK;
    }

    /**
     * Set the value of RETURN_PK
     *
     * @return  self
     */
    public function setReturnPK($return_pk) {
        $this->RETURN_PK = $return_pk;

        return $this;
    }

    /**
     * Get the value of AUDIT
     */
    public function getAudit(): bool {
        return $this->AUDIT;
    }

    /**
     * Set the value of AUDIT
     *
     * @return  self
     */
    public function setAudit($audit) {
        $this->AUDIT = $audit;

        return $this;
    }
}