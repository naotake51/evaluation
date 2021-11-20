<?php

namespace Naotake51\Evaluation\Nodes;

class BooleanNode implements Node {

    private string $expression;

    public function __construct(string $expression) {
        $this->expression = $expression;
    }

    public function eval() {
        return in_array($this->expression, ['true', 'True', 'TRUE'], true);
    }
}