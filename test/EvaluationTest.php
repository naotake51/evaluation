<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Evaluation;
use Naotake51\Evaluation\Errors\SyntaxError;
use Naotake51\Evaluation\Errors\UndefineFunctionError;
use Naotake51\Evaluation\Errors\ArgumentError;
use Closure;

class EvaluationTest extends TestCase {
    /**
     * testInvoke
     *
     * @param  Closure[] $functions
     * @param  Token[] $tokens
     * @param  Node|\Exception $expected
     * @return void
     *
     * @dataProvider dataInvoke
     */
    public function testInvoke(array $functions, string $expression, $expected): void {
        try {
            $evaluation = new Evaluation($functions);
            $result = $evaluation($expression);
            $this->assertSame($expected, $result);
        } catch (\Exception $e) {
            $this->assertEquals($expected, $e);
        }
    }

    public function dataInvoke(): array {
        return [
            '整数' => [
                'functions' => [],
                'expression' => '1',
                'expected' => 1
            ],
            '少数' => [
                'functions' => [],
                'expression' => '1.0',
                'expected' => 1.0
            ],
            '文字列' => [
                'functions' => [],
                'expression' => '"aaa"',
                'expected' => "aaa"
            ],
            'True' => [
                'functions' => [],
                'expression' => 'True',
                'expected' => true
            ],
            '加算' => [
                'functions' => [],
                'expression' => '1 + 1',
                'expected' => 2,
            ],
            '加算減算 複合' => [
                'functions' => [],
                'expression' => '1 + 2 - 3',
                'expected' => 0,
            ],
            '四則演算 複合' => [
                'functions' => [],
                'expression' => '1 + 2 * 3 - 4',
                'expected' => 3,
            ],
            'かっこ' => [
                'functions' => [],
                'expression' => '1 + 2 * (3 - 4)',
                'expected' => -1,
            ],
            'syntax error' => [
                'functions' => [],
                'expression' => '1 + ',
                'expected' => new SyntaxError('syntax error.'),
            ],
            '関数' => [
                'functions' => [
                    'hoge' => function ($arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge()',
                'expected' => 'hoge()'
            ],
            '関数 引数' => [
                'functions' => [
                    'hoge' => function ($arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge(1)',
                'expected' => 'hoge(1)'
            ],
            '関数 閉じ括弧なし' => [
                'functions' => [
                    'hoge' => function ($arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge(1',
                'expected' => new SyntaxError('syntax error.'),
            ],
            '関数 引数 複数' => [
                'functions' => [
                    'hoge' => function ($arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge(1, 2, 3)',
                'expected' => 'hoge(1,2,3)'
            ],
            '関数 引数が関数' => [
                'functions' => [
                    'hoge' => function ($arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                    'fuga' => function ($arguments) {
                        return 'fuga(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge(1, "aaa", fuga(True))',
                'expected' => 'hoge(1,aaa,fuga(1))'
            ],
            '未定義関数' => [
                'functions' => [
                    '*' => function ($identify, $arguments) {
                        return $identify . '(' . implode(',', $arguments) . ')';
                    }
                ],
                'expression' => 'aaa(1, 2, 3)',
                'expected' => 'aaa(1,2,3)'
            ],
            '関数定義なし' => [
                'functions' => [],
                'expression' => 'hoge(1)',
                'expected' => new UndefineFunctionError('function hoge is not exists.')
            ],
            'マジックメソッド オーバーライド' => [
                'functions' => [
                    '__add' => function ($arguments) {
                        return '__add(' . implode(',', $arguments) . ')';
                    }
                ],
                'expression' => '1 + 2',
                'expected' => '__add(1,2)'
            ],
            '引数の数が違う' => [
                'functions' => [
                    'hoge' => [
                        'function' => function ($arguments) {
                            return $arguments;
                        },
                        'arguments' => ['numeric', 'numeric']
                    ]
                ],
                'expression' => 'hoge(1, 2, 3)',
                'expected' => new ArgumentError('function hoge arguments not match 2.')
            ],
            '関数定義複数 パラメーター定義' => [
                'functions' => [
                    'hoge' => [
                        'function' => function ($arguments) {
                            return 'hoge(' . implode(',', $arguments) . ')';
                        },
                        'arguments' => ['integer', 'string', 'string']
                    ],
                    'fuga' => [
                        'function' => function ($arguments) {
                            return 'fuga(' . implode(',', $arguments) . ')';
                        },
                        'arguments' => ['bool']
                    ]
                ],
                'expression' => 'hoge(1, "aaa", fuga(True))',
                'expected' => 'hoge(1,aaa,fuga(1))'
            ],
            '引数タイプ' => [
                'functions' => [],
                'expression' => '"1" + 2',
                'expected' => 3
            ],
            '引数タイプエラー' => [
                'functions' => [],
                'expression' => '"aaa" + 2',
                'expected' => new ArgumentError('function __add argument 1 need numeric type.')
            ],
            'syntax error (' => [
                'functions' => [],
                'expression' => '(',
                'expected' => new SyntaxError('syntax error.')
            ],
            'syntax error (( 1 )' => [
                'functions' => [],
                'expression' => '(( 1 )',
                'expected' => new SyntaxError('syntax error.')
            ],
            'syntax error (( 1 )))' => [
                'functions' => [],
                'expression' => '(( 1 )))',
                'expected' => new SyntaxError('syntax error.')
            ],
        ];
    }

    public function testReadMe(): void {
        $evaluation = new Evaluation([
            'square' => function (array $arguments) {
                return $arguments[0] * $arguments[0];
            }
        ]);
        $result = $evaluation('square(2) + square(2)'); // => 8
        $this->assertSame(8, $result);

        $evaluation = new Evaluation([
            '__add' => function (array $arguments) {
                return "$arguments[0] + $arguments[1]";
            }
        ]);
        $result = $evaluation('1 + 2'); // => '1 + 2'
        $this->assertSame('1 + 2', $result);

        $evaluation = new Evaluation([
            '*' => function (string $identify, array $arguments) {
                return 'call ' . $identify . '(' . implode(', ', $arguments). ')';
            }
        ]);
        $result = $evaluation('hoge(1, 2)'); // => 'call hoge(1, 2)'
        $this->assertSame('call hoge(1, 2)', $result);

        $evaluation = new Evaluation([
            'repeat' => [
                'function' => function (array $arguments) {
                    return str_repeat($arguments[0], $arguments[1]);
                },
                'arguments' => ['string', 'numeric']
            ]
        ]);
        $result = $evaluation("repeat('abc', 3)"); // => 'abcabcabc'
        $this->assertSame('abcabcabc', $result);
    }
}