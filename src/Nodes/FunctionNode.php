<?php

namespace Naotake51\Evaluation\Nodes;

use Closure;
class FunctionNode implements Node {

    private string $identify;
    private array $arguments;

    public function __construct(string $identify, array $arguments) {
        $this->identify = $identify;
        $this->arguments = $arguments;
    }

    public function eval(Closure $callback) {
        return $callback(
            $this->identify,
            array_map(function (Node $argument) use ($callback) {
                return $argument->eval($callback);
            }, $this->arguments)
        );
    }
}