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
                'numeric, numeric' => function (array $arguments) {
                    return $arguments[0] + $arguments[1];
                }
            ],
            '__sub' => [
                'numeric, numeric' => function (array $arguments) {
                    return $arguments[0] - $arguments[1];
                },
            ],
            '__mul' => [
                'numeric, numeric' => function (array $arguments) {
                    return $arguments[0] * $arguments[1];
                },
            ],
            '__div' => [
                'numeric, numeric' => function (array $arguments) {
                    return $arguments[0] / $arguments[1];
                },
            ],
            '__mod' => [
                'numeric, numeric' => function (array $arguments) {
                    return $arguments[0] % $arguments[1];
                },
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
        $argumentOverload = new ArgumentOverload();
        return $rootNode->eval(
            function ($identify, $arguments) use ($functions, $argumentOverload) {
                if (array_key_exists($identify, $functions)) {
                    $function = $functions[$identify];
                    if (is_array($function)) {
                        $function = $argumentOverload($identify, $arguments, $function);
                    }
                    return $function($arguments);
                } else {
                    return $functions['*']($identify, $arguments);
                }
            }
        );
    }
}
