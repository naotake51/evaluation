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
            '__add' => [
                'function' => function ($arguments) {
                    return $arguments[0] + $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
            '__sub' => [
                'function' => function ($arguments) {
                    return $arguments[0] - $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
            '__mul' => [
                'function' => function ($arguments) {
                    return $arguments[0] * $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
            '__div' => [
                'function' => function ($arguments) {
                    return $arguments[0] / $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
            '__mod' => [
                'function' => function ($arguments) {
                    return $arguments[0] % $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
        ];
    }

    public function __invoke(string $expression) {
        $parser = new Parser();
        $tokens = $parser($expression);

        $functions = $this->functions;
        $argumentValidator = new ArgumentValidator();
        $callback = function ($identify, $arguments) use ($functions, $argumentValidator) {
            if (array_key_exists($identify, $functions)) {
                $function = $functions[$identify];
                if (is_array($function)) {
                    $argumentValidator($identify, $arguments, $function['arguments']);
                    $function = $function['function'];
                }
                return $function($arguments);
            } else if (array_key_exists('*', $functions)) {
                return $functions['*']($identify, $arguments);
            } else {
                throw new \Exception("function $identify is not exists");
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
