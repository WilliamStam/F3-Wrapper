<?php
namespace models;

interface SchemaInterface {
    function load($item);
    function item();
    function toArray();
}