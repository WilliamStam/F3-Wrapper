<?php
namespace models;
trait DBfetchTrait {
    
    function _select($select) {
        $this->query()->setSelect($select);
        return $this;
    }
    function _from($from) {
        $this->query()->setFrom($from);
        return $this;
    }
    function _group($group) {
        $this->query()->setGroup($group);
        return $this;
    }
    function _order($order) {
        $this->query()->setOrder($order);
        return $this;
    }

    function _where($sql, $params = array()) {
        $this->query()->setWhere($sql);
        $this->query()->setParams($params);
        return $this;
    }
    function setParams($params = array()) {
        $this->query()->setParams($params);
        return $this;
    }

    function _limit($limit) {
        $this->query()->setLimit($limit);

        return $this;
    }



}