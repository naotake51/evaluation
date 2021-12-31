<?php

namespace Naotake51\Evaluation\Nodes;

use Closure;

interface Node
{
    public function eval(Closure $callback);
}
