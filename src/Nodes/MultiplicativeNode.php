<?php

namespace Naotake51\Evaluation\Nodes;

class MultiplicativeNode implements Node {

    public function __construct(Node $left, Node $right, string $operator) {
        $this->left = $left;
        $this->right = $right;
        $this->operator = $operator;
    }

    public function eval() {
        $leftValue = $this->left->eval();
        $rightValue = $this->right->eval();

        if ($this->operator === '*') {
            return $leftValue * $rightValue;
        } else if ($this->operator === '/') {
            return $leftValue / $rightValue;
        } else {
            return $leftValue % $rightValue;
        }
    }
}