<?php

namespace Naotake51\Evaluation;

class Token {
    /** @var string */
    public string $type;

    /** @var string */
    public string $expression;

    /**
     * @param  string $type
     * @param  string $expression
     * @return void
     */
    public function __construct(string $type, string $expression) {
        $this->type = $type;
        $this->expression = $expression;
    }
}