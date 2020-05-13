<?php

namespace permissions;

abstract class AbstractPermission {
    CONST ROLE_TYPE_SYSTEM = '1';
    // CONST ROLE_TYPE_INSTANCE = '2';

    /**
     * Get the value of description
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Get the value of type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get the value of group
     */
    public function getGroup() {
        return $this->group;
    }
    /**
     * Get the value of label
     */
    public function getLabel() {
        return $this->label;
    }

}
