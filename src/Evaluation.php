<?php

namespace Naotake51\Evaluation;

use Naotake51\Evaluation\Errors\SyntaxError;
use Naotake51\Evaluation\Errors\UndefineFunctionError;
use Naotake51\Evaluation\Errors\ArgumentError;
use Closure;

/**
 * 式評価クラス
 */
class Evaluation
{
    /** @var Closure[] */
    private array $functions;

    /**
     * @param  @var (Closure|array)[] $functions
     */
    public function __construct(array $functions)
    {
        $defaults = [
            '__add' => [
                'numeric, numeric' => function ($a, $b) {
                    return $a + $b;
                }
            ],
            '__sub' => [
                'numeric, numeric' => function ($a, $b) {
                    return $a - $b;
                },
            ],
            '__mul' => [
                'numeric, numeric' => function ($a, $b) {
                    return $a * $b;
                },
            ],
            '__div' => [
                'numeric, numeric' => function ($a, $b) {
                    return $a / $b;
                },
            ],
            '__mod' => [
                'numeric, numeric' => function ($a, $b) {
                    return $a % $b;
                },
            ],
            '__equal_strict' => [
                'mixed, mixed' => function ($a, $b) {
                    return $a === $b;
                },
            ],
            '__not_equal_strict' => [
                'mixed, mixed' => function ($a, $b) {
                    return $a !== $b;
                },
            ],
            '__equal' => [
                'mixed, mixed' => function ($a, $b) {
                    return $a == $b;
                },
            ],
            '__not_equal' => [
                'mixed, mixed' => function ($a, $b) {
                    return $a != $b;
                },
            ],
            '__and' => [
                'mixed, mixed' => function ($a, $b) {
                    return $a && $b;
                },
            ],
            '__or' => [
                'mixed, mixed' => function ($a, $b) {
                    return $a || $b;
                },
            ],
            '__not' => [
                'mixed' => function ($a) {
                    return !$a;
                },
            ],
        ];

        $this->functions = $functions + $defaults;
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
    public function __invoke(string $expression)
    {
        $lexer = new Lexer();
        $tokens = $lexer($expression);
        if (count($tokens) === 0) {
            return null;
        }

        $parser = new Parser();
        $rootNode = $parser($tokens);

        $functions = $this->functions;
        $argumentsResolver = new ArgumentsResolver();
        return $rootNode->eval(
            // ファンクションや演算子を解決するコールバック
            function ($identify, $arguments) use ($functions, $argumentsResolver) {
                if (array_key_exists($identify, $functions)) {
                    $function = $functions[$identify];

                    if (is_array($function)) {
                        // パラメーターのパターンごとにコールバックが設定されている
                        $mappedArgsFunctions = $function;
                        $resolveFunction = $argumentsResolver($arguments, $mappedArgsFunctions);
                        if ($resolveFunction === null) {
                            throw new ArgumentError("function $identify arguments is not match (" . implode(') or (', array_keys($mappedArgsFunctions)) . ').');
                        }
                        return $resolveFunction(...$arguments);
                    } else {
                        // 関数名に対して１つコールバックが設定されている
                        return $function($arguments);
                    }
                } else if (array_key_exists('*', $functions)) {
                    // 関数名に対してコールバックが設定されていないが、'*'が登録されている
                    return $functions['*']($identify, $arguments);
                } else {
                    // 対応するコールバックなし
                    throw new UndefineFunctionError("function $identify is not exists.");
                }
            }
        );
    }
}
