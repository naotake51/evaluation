<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Evaluation;
use Naotake51\Evaluation\Errors\EvaluationError;
use Naotake51\Evaluation\Errors\SyntaxError;
use Naotake51\Evaluation\Errors\UndefineFunctionError;
use Naotake51\Evaluation\Errors\ArgumentError;
use Closure;

class EvaluationTest extends TestCase
{
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
    public function testInvoke(array $functions, string $expression, $expected): void
    {
        try {
            $evaluation = new Evaluation($functions);
            $result = $evaluation($expression);
            $this->assertSame($expected, $result);
        } catch (\Exception $e) {
            $this->assertEquals($expected, $e);
        }
    }

    public function dataInvoke(): array
    {
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
                    'hoge' => function (array $arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge()',
                'expected' => 'hoge()'
            ],
            '関数 引数' => [
                'functions' => [
                    'hoge' => function (array $arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge(1)',
                'expected' => 'hoge(1)'
            ],
            '関数 閉じ括弧なし' => [
                'functions' => [
                    'hoge' => function (array $arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge(1',
                'expected' => new SyntaxError('syntax error.'),
            ],
            '関数 引数 複数' => [
                'functions' => [
                    'hoge' => function (array $arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge(1, 2, 3)',
                'expected' => 'hoge(1,2,3)'
            ],
            '関数 引数が関数' => [
                'functions' => [
                    'hoge' => function (array $arguments) {
                        return 'hoge(' . implode(',', $arguments) . ')';
                    },
                    'fuga' => function (array $arguments) {
                        return 'fuga(' . implode(',', $arguments) . ')';
                    },
                ],
                'expression' => 'hoge(1, "aaa", fuga(True))',
                'expected' => 'hoge(1,aaa,fuga(1))'
            ],
            '未定義関数' => [
                'functions' => [
                    '*' => function (string $identify, array $arguments) {
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
                    '__add' => function (array $arguments) {
                        return '__add(' . implode(',', $arguments) . ')';
                    }
                ],
                'expression' => '1 + 2',
                'expected' => '__add(1,2)'
            ],
            '引数の数が違う' => [
                'functions' => [
                    'hoge' => [
                        'numeric, numeric' => function (array $arguments) {
                            return $arguments;
                        }
                    ]
                ],
                'expression' => 'hoge(1, 2, 3)',
                'expected' => new ArgumentError('function hoge arguments is not match (numeric, numeric).')
            ],
            '関数定義複数 パラメーター定義' => [
                'functions' => [
                    'hoge' => [
                        'integer, string, string' => function (...$arguments) {
                            return 'hoge(' . implode(',', $arguments) . ')';
                        }
                    ],
                    'fuga' => [
                        'bool' => function (...$arguments) {
                            return 'fuga(' . implode(',', $arguments) . ')';
                        },
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
                'expected' => new ArgumentError('function __add arguments is not match (numeric, numeric).')
            ],
            'パラメーター　オーバーロード' => [
                'functions' => [
                    'hoge' => [
                        'numeric, numeric' => function (...$arguments) {
                            return 'hoge(numeric, numeric)';
                        },
                        'numeric, string' => function (...$arguments) {
                            return 'hoge(numeric, string)';
                        },
                    ]
                ],
                'expression' => 'hoge(1, 1)',
                'expected' => 'hoge(numeric, numeric)'
            ],
            'パラメーター　オーバーロード 2' => [
                'functions' => [
                    'hoge' => [
                        'numeric, numeric' => function (...$arguments) {
                            return 'hoge(numeric, numeric)';
                        },
                        'numeric, string' => function (...$arguments) {
                            return 'hoge(numeric, string)';
                        },
                    ]
                ],
                'expression' => 'hoge(1, "aaa")',
                'expected' => 'hoge(numeric, string)'
            ],
            'パラメーター　オーバーロード 3' => [
                'functions' => [
                    'hoge' => [
                        'numeric, numeric' => function (...$arguments) {
                            return 'hoge(numeric, numeric)';
                        },
                        'numeric, string' => function (...$arguments) {
                            return 'hoge(numeric, string)';
                        },
                    ]
                ],
                'expression' => 'hoge(1, True)',
                'expected' => new ArgumentError('function hoge arguments is not match (numeric, numeric) or (numeric, string).')
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
            '配列' => [
                'functions' => [],
                'expression' => '[1, 2, 1 * 1 / 1]',
                'expected' => [1, 2, 1]
            ],
            '配列 関数要素' => [
                'functions' => [
                    'hoge' => function (array $arguments) {
                        return 'hoge(' . implode(', ', $arguments) . ')';
                    }
                ],
                'expression' => '[1, 2, hoge(1, 2)]',
                'expected' => [1, 2, 'hoge(1, 2)']
            ],
            '多次元配列' => [
                'functions' => [
                    'hoge' => function (array $arguments) {
                        return 'hoge(' . implode(', ', $arguments) . ')';
                    },
                    'fuga' => function (array $arguments) {
                        return 'fuga(' . implode(', ', $arguments) . ')';
                    }
                ],
                'expression' => '[1, 2, hoge(1, 2), [1, 2, fuga(True)]]',
                'expected' => [1, 2, 'hoge(1, 2)', [1, 2, 'fuga(1)']]
            ],
            'オブジェクト' => [
                'functions' => [],
                'expression' => "{'a': 1, 'b': 2, 'c': {'c1': 1, 'c2': 2}}",
                'expected' => [
                    'a' => 1,
                    'b' => 2,
                    'c' => [
                        'c1' => 1,
                        'c2' => 2,
                    ],
                ]
            ],
            'オブジェクト 関数含む' => [
                'functions' => [
                    'hoge' => function (array $arguments) {
                        return 'hoge';
                    }
                ],
                'expression' => "{'a': 1, 'b': 2, 'c': {'c1': hoge(1, true), 'c2': 2}}",
                'expected' => [
                    'a' => 1,
                    'b' => 2,
                    'c' => [
                        'c1' => 'hoge',
                        'c2' => 2,
                    ],
                ]
            ],
            'オブジェクト 関数のパラメーター' => [
                'functions' => [
                    'hoge' => function (array $arguments) {
                        $object = $arguments[0];
                        return "hoge:{$object['a']}:{$object['b']}";
                    }
                ],
                'expression' => "hoge({'a': 1, 'b': 2})",
                'expected' => "hoge:1:2"
            ],
            '論理演算子' => [
                'functions' => [],
                'expression' => "1 === 1 && 2 !== 1",
                'expected' => true
            ],
            '論理演算子 2' => [
                'functions' => [],
                'expression' => "1 === 1 && 2 !== 2",
                'expected' => false
            ],
            '論理演算子 3' => [
                'functions' => [],
                'expression' => "1 === 1 || 2 !== 2",
                'expected' => true
            ],
            '論理演算子 4' => [
                'functions' => [],
                'expression' => "1 === 1 && !!!(2 !== 2)",
                'expected' => true
            ],
            '論理演算子 5' => [
                'functions' => [],
                'expression' => "1 === 1 && !!!(2 !== 2) || 1 === 2",
                'expected' => true
            ],
        ];
    }

    public function testReadMe(): void
    {
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
                return 'call ' . $identify . '(' . implode(', ', $arguments) . ')';
            }
        ]);
        $result = $evaluation('hoge(1, 2)'); // => 'call hoge(1, 2)'
        $this->assertSame('call hoge(1, 2)', $result);

        $evaluation = new Evaluation([
            'repeat' => [
                'string, integer|null' => function (string $str, ?int $repeat) {
                    return str_repeat($str, $repeat ?? 2);
                },
            ]
        ]);
        $result = $evaluation("repeat('abc', 3)"); // => 'abcabcabc'
        $this->assertSame('abcabcabc', $result);

        $evaluation = new Evaluation([
            '__add' => [
                'string, string' => function (string $a, string $b) {
                    return $a . $b;
                },
                'numeric, numeric' => function ($a, $b) {
                    return $a + $b;
                },
            ]
        ]);
        $result = $evaluation("'abc' + 'def'"); // => 'abcdef'
        $this->assertSame('abcdef', $result);

        try {
            $evaluation = new Evaluation([
                'hoge' => function (array $arguments) {
                    return 'hoge';
                },
            ]);
            $result = $evaluation("fuga()"); // => UndefineFunctionError
        } catch (EvaluationError $e) {
            error_log($e->getMessage()); // => 'function fuga is not exists.'
            $this->assertSame($e->getMessage(), 'function fuga is not exists.');
        }
    }
}
