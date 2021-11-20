<?php

namespace Naotake51\Evaluation;

use Closure;

class Evaluation {
    private Closure $callback;

    public function __construct(Closure $callback) {
        $this->callback = $callback;
    }

    public function __invoke(string $expression) {
        $parser = new Parser();
        $tokens = $parser($expression);

        $lexer = new Lexer($this->callback);
        $root = $lexer($tokens);

        if ($root === null) {
            return null;
        }

        return $root->eval();
    }
}
