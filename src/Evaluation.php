<?php

namespace Naotake51\Evaluation;

use Closure;

class Evaluation {
    /** @var Closure[] */
    private array $functions;

    /**
     * @param  @var Closure[] $functions
     */
    public function __construct(array $functions) {
        $this->functions = $functions + [
            // default functions
            '__add' => function ($arguments) {
                return $arguments[0] + $arguments[1];
            },
            '__sub' => function ($arguments) {
                return $arguments[0] - $arguments[1];
            },
            '__mul' => function ($arguments) {
                return $arguments[0] * $arguments[1];
            },
            '__div' => function ($arguments) {
                return $arguments[0] / $arguments[1];
            },
            '__mod' => function ($arguments) {
                return $arguments[0] % $arguments[1];
            },
        ];
    }

    public function __invoke(string $expression) {
        $parser = new Parser();
        $tokens = $parser($expression);

        $functions = $this->functions;
        $callback = function ($identify, $arguments) use ($functions) {
            if (array_key_exists($identify, $functions)) {
                return $functions[$identify]($arguments);
            } else if (array_key_exists('*', $functions)) {
                return $functions['*']($identify, $arguments);
            } else {
                throw new \Exception("$identify is not exists function.");
            }
        };
        $lexer = new Lexer($callback);
        $root = $lexer($tokens);

        if ($root === null) {
            return null;
        }

        return $root->eval();
    }
}