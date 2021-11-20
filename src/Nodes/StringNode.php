<?php

namespace Naotake51\Evaluation\Nodes;

class StringNode implements Node {

    private string $expression;

    public function __construct(string $expression) {
        $this->expression = $expression;
    }

    public function eval() {
        return mb_substr($this->expression, 1, strlen($this->expression) - 2); // TODO エスケープ文字を考慮していない。
    }
}