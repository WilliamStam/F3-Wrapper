<?php
namespace system\db;
class Query {
    protected $DB = null;
    protected
        $SELECT = null,
        $FROM = null,
        $WHERE = null,
        $HAVING = null,
        $LIMIT = null,
        $ORDER = null,
        $GROUP = null,
        $PARAMS = array()
    ;

    protected
        $TTL = 0
    ;
    protected $BACK = null;

    function __construct($DB_connection = null) {
        $this->system = \Base::instance();
       
        $this->DB = $DB_connection;
        if (!$this->DB) {
            $this->DB = $this->system->get("DB");
        }
    }

   
    function sql() {
        $sql = array();

        if ($this->getSelect()) {
            $sql[] = "SELECT " . $this->getSelect();
        } else {
            $sql[] = "SELECT *";
        }

        if ($this->getFrom()) {
            $sql[] = "FROM {$this->getFrom()}";
        }

        if ($this->getWhere()) {
            $sql[] = "WHERE " . $this->getWhere();
        }

        if ($this->getGroup()) {
            $sql[] = "GROUP BY " . $this->getGroup();
        }

        if ($this->getHaving()) {
            $sql[] = "HAVING " . $this->getHaving;
        }

        if ($this->getOrder()) {
            $sql[] = "ORDER BY " .$this->getOrder();
        }

        if ($this->getLimit()) {
            $sql[] = "LIMIT " . $this->getLimit();
        }

        $sql = implode(" " . PHP_EOL, (array) $sql);

        //$this->system->debug($sql);

        return $sql;

    }

    function fetch() {
        $sql = $this->sql();
        $params = $this->getParams();

        $records = $this->DB->exec($sql, $params);

        return $records;
    }

    /**
     * Get the value of SELECT
     */
    public function getSelect() {
        return $this->SELECT;
    }

    /**
     * Set the value of SELECT
     *
     * @return  self
     */
    public function setSelect($select) {
        $this->SELECT = $select;

        return $this;
    }

    /**
     * Get the value of FROM
     */
    public function getFrom() {
        return $this->FROM;
    }

    /**
     * Set the value of FROM
     *
     * @return  self
     */
    public function setFrom($from) {
        $this->FROM = $from;

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
    public function setWhere($where,$params=null) {
        $this->WHERE = $where;
        if ($params){
            $this->setParams($params);
        }

        return $this;
    }

    /**
     * Get the value of HAVING
     */
    public function getHaving() {
        return $this->HAVING;
    }

    /**
     * Set the value of HAVING
     *
     * @return  self
     */
    public function setHaving($having) {
        $this->HAVING = $having;

        return $this;
    }

    /**
     * Get the value of LIMIT
     */
    public function getLimit() {
        return $this->Limit;
    }

    /**
     * Set the value of LIMIT
     *
     * @return  self
     */
    public function setLimit($limit) {
        $this->LIMIT = $limit;

        return $this;
    }

    /**
     * Get the value of ORDER
     */
    public function getOrder() {
        return $this->ORDER;
    }

    /**
     * Set the value of ORDER
     *
     * @return  self
     */
    public function setOrder($order) {
        $this->ORDER = $order;

        return $this;
    }

    /**
     * Get the value of GROUP
     */
    public function getGroup() {
        return $this->GROUP;
    }

    /**
     * Set the value of GROUP
     *
     * @return  self
     */
    public function setGroup($group) {
        $this->GROUP = $group;

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
}