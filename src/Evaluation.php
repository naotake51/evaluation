<?php

namespace Naotake51\Evaluation;

class Evaluation {
    public function __invoke(string $expression) {
        $parser = new Parser();
        $tokens = $parser($expression);

        $lexer = new Lexer();
        $root = $lexer($tokens);

        if ($root === null) {
            return null;
        }

        return $root->eval();
    }
}
