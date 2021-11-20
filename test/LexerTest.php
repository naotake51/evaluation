<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Lexer;
use Naotake51\Evaluation\Token;
use Naotake51\Evaluation\Nodes\Node;
use Naotake51\Evaluation\Nodes\IntegerNode;
use Naotake51\Evaluation\Nodes\FloatNode;
use Naotake51\Evaluation\Nodes\StringNode;
use Naotake51\Evaluation\Nodes\BooleanNode;
use Naotake51\Evaluation\Nodes\AdditiveNode;
use Naotake51\Evaluation\Nodes\MultiplicativeNode;
use Naotake51\Evaluation\Nodes\FunctionNode;
use Closure;

class LexerTest extends TestCase {
    /**
     * testInvoke
     *
     * @param  Token[] $tokens
     * @param  Node|\Exception $expected
     * @return void
     *
     * @dataProvider dataInvoke
     */
    public function testInvoke(array $tokens, $expected): void {
        // dummy
        $callback = function (string $identify, array $arguments) {
            return null;
        };

        try {
            $lexer = new Lexer($callback);
            $root = $lexer($tokens);
            $this->assertEquals($expected, $root);
        } catch (\Exception $e) {
            $this->assertEquals($expected, $e);
        }
    }

    public function dataInvoke(): array {
        // dummy
        $callback = function (string $identify, array $arguments) {
            return null;
        };

        return [
            '整数' => [
                'tokens' => [
                    new Token('INTEGER', '1'),
                ],
                'expected' => new IntegerNode('1')
            ],
            '少数' => [
                'tokens' => [
                    new Token('FLOAT', '1.0'),
                ],
                'expected' => new FloatNode('1.0')
            ],
            '文字列' => [
                'tokens' => [
                    new Token('STRING', '"aaa"'),
                ],
                'expected' => new StringNode('"aaa"')
            ],
            'True' => [
                'tokens' => [
                    new Token('BOOLEAN', 'True'),
                ],
                'expected' => new BooleanNode('True')
            ],
            '加算' => [
                'tokens' => [
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('INTEGER', '1'),
                ],
                'expected' => new AdditiveNode(
                    new IntegerNode('1'),
                    new IntegerNode('1'),
                    '+'
                ),
            ],
            '加算減算 複合' => [
                'tokens' => [
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('INTEGER', '2'),
                    new Token('OPERATOR', '-'),
                    new Token('INTEGER', '3'),
                ],
                'expected' => new AdditiveNode(
                    new AdditiveNode(
                        new IntegerNode('1'),
                        new IntegerNode('2'),
                        '+'
                    ),
                    new IntegerNode('3'),
                    '-'
                ),
            ],
            '四則演算 複合' => [
                'tokens' => [
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('INTEGER', '2'),
                    new Token('OPERATOR', '*'),
                    new Token('INTEGER', '3'),
                    new Token('OPERATOR', '-'),
                    new Token('INTEGER', '4'),
                ],
                'expected' => new AdditiveNode(
                    new AdditiveNode(
                        new IntegerNode('1'),
                        new MultiplicativeNode(
                            new IntegerNode('2'),
                            new IntegerNode('3'),
                            '*'
                        ),
                        '+'
                    ),
                    new IntegerNode('4'),
                    '-'
                ),
            ],
            'かっこ' => [
                'tokens' => [
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('INTEGER', '2'),
                    new Token('OPERATOR', '*'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '3'),
                    new Token('OPERATOR', '-'),
                    new Token('INTEGER', '4'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new AdditiveNode(
                    new IntegerNode('1'),
                    new MultiplicativeNode(
                        new IntegerNode('2'),
                        new AdditiveNode(
                            new IntegerNode('3'),
                            new IntegerNode('4'),
                            '-'
                        ),
                        '*'
                    ),
                    '+'
                ),
            ],
            'Syntax Error' => [
                'tokens' => [
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                ],
                'expected' => new \Exception('Syntax Error'),
            ],
            '関数' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new FunctionNode('hoge', [], $callback)
            ],
            '関数 引数' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '1'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new FunctionNode(
                    'hoge',
                    [
                        new IntegerNode('1')
                    ],
                    $callback
                )
            ],
            '関数 閉じ括弧なし' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '1'),
                ],
                'expected' => new \Exception('Syntax Error'),
            ],
            '関数 引数 複数' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '1'),
                    new Token('COMMA', ','),
                    new Token('INTEGER', '2'),
                    new Token('COMMA', ','),
                    new Token('INTEGER', '3'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new FunctionNode(
                    'hoge',
                    [
                        new IntegerNode('1'),
                        new IntegerNode('2'),
                        new IntegerNode('3'),
                    ],
                    $callback
                )
            ],
            '関数 引数が関数' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '1'),
                    new Token('COMMA', ','),
                    new Token('STRING', '"aaa"'),
                    new Token('COMMA', ','),
                    new Token('IDENT', 'fuga'),
                    new Token('L_PAREN', '('),
                    new Token('BOOLEAN', 'TRUE'),
                    new Token('R_PAREN', ')'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new FunctionNode(
                    'hoge',
                    [
                        new IntegerNode('1'),
                        new StringNode('"aaa"'),
                        new FunctionNode(
                            'fuga',
                            [
                                new BooleanNode('TRUE'),
                            ],
                            $callback
                        )
                    ],
                    $callback
                )
            ],
            '四則演算 + 関数' => [
                'tokens' => [
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('INTEGER', '2'),
                    new Token('COMMA', ','),
                    new Token('INTEGER', '2'),
                    new Token('COMMA', ','),
                    new Token('IDENT', 'fuga'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '1'),
                    new Token('R_PAREN', ')'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new AdditiveNode(
                    new IntegerNode('1'),
                    new FunctionNode(
                        'hoge',
                        [
                            new AdditiveNode(
                                new IntegerNode('1'),
                                new IntegerNode('2'),
                                '+'
                            ),
                            new IntegerNode('2'),
                            new FunctionNode(
                                'fuga',
                                [
                                    new IntegerNode('1'),
                                ],
                                $callback
                            ),
                        ],
                        $callback
                    ),
                    '+'
                )
            ],
      ];
    }
}