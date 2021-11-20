<?php

namespace Naotake51\Evaluation\Nodes;

use Closure;
class FunctionNode implements Node {

    private string $identify;
    private array $arguments;
    private Closure $callback;

    public function __construct(string $identify, array $arguments, Closure $callback) {
        $this->identify = $identify;
        $this->arguments = $arguments;
        $this->callback = $callback;
    }

    public function eval() {
        $callback = $this->callback;

        return $callback(
            $this->identify,
            array_map(function (Node $argument) {
                return $argument->eval();
            }, $this->arguments)
        );
    }
}