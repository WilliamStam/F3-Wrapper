<?php
namespace models;
trait DBfetchTrait {

    protected $SQL = array(
        "SELECT" => null,
        "FROM" => null,
        "WHERE" => null,
        "HAVING" => null,
        "LIMIT" => null,
        "ORDER" => null,
        "GROUP" => null,
        "PARAMS" => array(),
    );
    protected $ALLOWINSERT = true;

    protected
    $TTL = 0
    ;

    function _allowinsert($trueorfalse) {
        $this->ALLOWINSERT = $trueorfalse;

        return $this;
    }
    function _select($select) {
        $this->SQL['SELECT'] = $select;

        return $this;
    }
    function _from($from) {
        $this->SQL['FROM'] = $from;

        return $this;
    }
    function _group($group) {
        $this->SQL['GROUP'] = $group;

        return $this;
    }
    function _order($order) {
        $this->SQL['ORDER'] = $order;

        return $this;
    }

    function _where($sql, $params = array()) {
        $this->SQL['WHERE'] = $sql;
        $this->setParams($params);

        return $this;
    }
    function setParams($params = array()) {
        $this->SQL['PARAMS'] = $params;

        return $this;
    }

    function _limit($limit) {
        $this->SQL['LIMIT'] = $limit;

        return $this;
    }
    function _ttl($ttl) {
        $this->TTL = $ttl;

        return $this;
    }

    function sql() {
        $sql = array();

        if ($this->SQL['SELECT']) {
            $sql[] = "SELECT " . $this->SQL['SELECT'];
        } else {
            $sql[] = "SELECT *";
        }

        if ($this->SQL['FROM']) {
            $sql[] = "FROM {$this->SQL['FROM']}";
        }

        if ($this->SQL['WHERE']) {
            $sql[] = "WHERE " . $this->SQL['WHERE'];
        }

        if ($this->SQL['GROUP']) {
            $sql[] = "GROUP BY " . $this->SQL['GROUP'];
        }

        if ($this->SQL['HAVING']) {
            $sql[] = "HAVING " . $this->SQL['HAVING'];
        }

        if ($this->SQL['ORDER']) {
            $sql[] = "ORDER BY " . $this->SQL['ORDER'];
        }

        if ($this->SQL['LIMIT']) {
            $sql[] = "LIMIT " . $this->SQL['LIMIT'];
        }

        $sql = implode(" " . PHP_EOL, (array) $sql);

        //$this->system->debug($sql);

        return $sql;

    }

    function fetch_data(){
        $sql = $this->sql();
        $params = $this->SQL['PARAMS'];

        $records = $this->DB->exec($sql,$params);



        return $records;
    }
}