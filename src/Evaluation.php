<?php

namespace Naotake51\Evaluation;

use Naotake51\Evaluation\Errors\SyntaxError;
use Naotake51\Evaluation\Errors\UndefineFunctionError;
use Naotake51\Evaluation\Errors\ArgumentError;
use Closure;

/**
 * 式評価クラス
 */
class Evaluation {
    /** @var Closure[] */
    private array $functions;

    /**
     * @param  @var (Closure|array)[] $functions
     */
    public function __construct(array $functions) {
        $this->functions = $functions + [
            // default functions
            '__add' => [
                'function' => function (array $arguments) {
                    return $arguments[0] + $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
            '__sub' => [
                'function' => function (array $arguments) {
                    return $arguments[0] - $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
            '__mul' => [
                'function' => function (array $arguments) {
                    return $arguments[0] * $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
            '__div' => [
                'function' => function (array $arguments) {
                    return $arguments[0] / $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
            '__mod' => [
                'function' => function (array $arguments) {
                    return $arguments[0] % $arguments[1];
                },
                'arguments' => ['numeric', 'numeric']
            ],
            '*' => function (string $identify, array $arguments) {
                throw new UndefineFunctionError("function $identify is not exists.");
            }
        ];
    }

    /**
     * 式評価
     *
     * @param  string $expression
     * @return mixed
     * @throws SyntaxError
     * @throws UndefineFunctionError
     * @throws ArgumentError
     */
    public function __invoke(string $expression) {
        $parser = new Parser();
        $tokens = $parser($expression);
        if (count($tokens) === 0) {
            return null;
        }

        $lexer = new Lexer();
        $rootNode = $lexer($tokens);

        $functions = $this->functions;
        $argumentValidator = new ArgumentValidator();
        return $rootNode->eval(
            function ($identify, $arguments) use ($functions, $argumentValidator) {
                if (array_key_exists($identify, $functions)) {
                    $function = $functions[$identify];
                    if (is_array($function)) {
                        $argumentValidator($identify, $arguments, $function['arguments']);
                        $function = $function['function'];
                    }
                    return $function($arguments);
                } else {
                    return $functions['*']($identify, $arguments);
                }
            }
        );
    }
}
