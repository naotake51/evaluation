<?php

namespace Naotake51\Evaluation;

final class Arr {
    public static function merge($array, $merge) {
        if (is_array($array) && is_array($merge)) {
            foreach ($merge as $key => $value) {
                $array[$key] = array_key_exists($key, $array) ? self::merge($array[$key], $value) : $value;
            }
        }
        return $array;
    }
}
