<?php

namespace Naotake51\Evaluation\Nodes;

class AdditiveNode implements Node {

    private Node $left;
    private Node $right;
    private string $operator;

    public function __construct(Node $left, Node $right, string $operator) {
        $this->left = $left;
        $this->right = $right;
        $this->operator = $operator;
    }

    public function eval() {
        $leftValue = $this->left->eval();
        $rightValue = $this->right->eval();

        if ($this->operator === '+') {
            return $leftValue + $rightValue;
        } else {
            return $leftValue - $rightValue;
        }
    }
}