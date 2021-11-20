<?php

namespace Naotake51\Evaluation\Nodes;

use Closure;

class BooleanNode implements Node {

    private string $expression;

    public function __construct(string $expression) {
        $this->expression = $expression;
    }

    public function eval(Closure $callback) {
        return in_array($this->expression, ['true', 'True', 'TRUE'], true);
    }
}