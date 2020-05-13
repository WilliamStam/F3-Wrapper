<?php

namespace System\utilities;

class Arrays {
    public static function getValue($array, $key, $default = NULL) {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::getValue($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== FALSE) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            // this is expected to fail if the property does not exist, or __get() is not implemented
            // it is not reliably possible to check whether a property is accessible beforehand
            return $array->$key;
        } else if (is_array($array)) {
            return (isset($array[$key]) || array_key_exists($key, $array)) ? $array[$key] : $default;
        }

        return $default;
    }

    public static function merge($a, $b) {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            foreach (array_shift($args) as $k => $v) {
                if (is_int($k)) {
                    if (array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } else if (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

    public static function replace() {
        $args = func_get_args();
        $return = array_shift($args);

        foreach ($args as $arg) {
            $return = self::_replace($return, $arg);
        }

        return $return;

    }

    private static function _replace($source, $replace) {

        $return = array();

        foreach ($source as $k => $v) {
            if (isset($replace[$k])) {
                if (is_array($v)) {
                    if (count(array_filter(array_keys($v), 'is_string')) > 0) {
                        $v = self::replace($v, $replace[$k]);
                    } else {
                        $v = $replace[$k];
                    }

                } else {
                    $v = $replace[$k];
                }

            }
            $return[$k] = $v;
        }

        return $return;
    }
    static function removeKeyFromArray(&$array, $key_to_remove) {
        foreach ($array as $key => &$value) {
            if ($key === $key_to_remove) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                self::removeKeyFromArray($value, $key_to_remove);
            }
        }
    }

   
}