<?php

namespace Naotake51\Evaluation\Nodes;

class FunctionNode implements Node {

    private string $identify;
    private array $arguments;

    public function __construct(string $identify, array $arguments) {
        $this->identify = $identify;
        $this->arguments = $arguments;
    }

    public function eval() {
        $evalList = array_map(function (Node $argument) {
            return $argument->eval();
        }, $this->arguments);

        return $this->identify . '(' . implode(',', $evalList) . ')'; // TODO
    }
}