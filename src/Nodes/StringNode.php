<?php

namespace Naotake51\Evaluation\Nodes;

use Closure;

class StringNode implements Node {

    private string $expression;

    public function __construct(string $expression) {
        $this->expression = $expression;
    }

    public function eval(Closure $callback) {
        $inner = mb_substr($this->expression, 1, strlen($this->expression) - 2); // 端のクォート文字を除外
        return preg_replace('/\\\\(.)/', '${1}', $inner); // エスケープ文字を除外
    }
}