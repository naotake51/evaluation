<?php

namespace Naotake51\Evaluation\Nodes;

use Closure;

class ObjectNode implements Node
{
    private array $object;

    public function __construct(array $object)
    {
        $this->object = $object;
    }

    public function eval(Closure $callback)
    {
        $result = [];
        foreach ($this->object as $prop) {
            $key = $prop['key']->eval($callback);
            $value = $prop['value']->eval($callback);
            $result[$key] = $value;
        }
        return $result;
    }
}
