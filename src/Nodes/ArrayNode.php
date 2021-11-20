<?php

namespace Naotake51\Evaluation\Nodes;

use Closure;

class ArrayNode implements Node {

    private array $items;

    public function __construct(array $items) {
        $this->items = $items;
    }

    public function eval(Closure $callback) {
        return array_map(function (Node $item) use ($callback) {
            return $item->eval($callback);
        }, $this->items);
    }
}