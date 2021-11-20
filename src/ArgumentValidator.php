<?php

namespace Naotake51\Evaluation;

class ArgumentValidator {
    public function __invoke(string $identify, array $arguments, array $defines) {
        if (count($defines) !== count($arguments)) {
            throw new \Exception("function $identify arguments not match " . count($defines) . '.');
        }

        foreach ($defines as $i => $define) {
            $types = explode('|', $define);
            if (!$this->matchType($arguments[$i], $types)) {
                $number = $i + 1;
                throw new \Exception("function $identify argument $number need $define type.");
            }
        }
    }

    private function matchType($value, array $types) {
        foreach ($types as $type) {
            if ($type === 'numeric' && is_numeric($value)) {
                return true;
            } elseif ($type === 'integer' && is_integer($value)) {
                return true;
            } elseif ($type === 'float' && is_float($value)) {
                return true;
            } elseif ($type === 'string' && is_string($value)) {
                return true;
            } elseif ($type === 'bool' && is_bool($value)) {
                return true;
            } elseif ($type === 'null' && is_null($value)) {
                return true;
            }
        }
        return false;
    }
}
