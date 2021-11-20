<?php

namespace Naotake51\Evaluation\Nodes;

class FloatNode implements Node {

    private string $expression;

    public function __construct(string $expression) {
        $this->expression = $expression;
    }

    public function eval() {
        return (float)$this->expression;
    }
}