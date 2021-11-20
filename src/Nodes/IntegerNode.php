<?php

namespace Naotake51\Evaluation\Nodes;

use Closure;

class IntegerNode implements Node {

    private string $expression;

    public function __construct(string $expression) {
        $this->expression = $expression;
    }

    public function eval(Closure $callback) {
        return (int)$this->expression;
    }
}