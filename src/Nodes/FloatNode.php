<?php

namespace Naotake51\Evaluation\Nodes;

use Closure;

class FloatNode implements Node
{
    private string $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function eval(Closure $callback)
    {
        return (float)$this->expression;
    }
}
