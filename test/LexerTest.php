<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Lexer;
use Naotake51\Evaluation\Token;
use Naotake51\Evaluation\Nodes\Node;
use Naotake51\Evaluation\Nodes\NumberNode;
use Naotake51\Evaluation\Nodes\AdditiveNode;
use Naotake51\Evaluation\Nodes\MultiplicativeNode;
use Naotake51\Evaluation\Nodes\FunctionNode;

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
        try {
            $lexer = new Lexer();
            $root = $lexer($tokens);
            $this->assertEquals($expected, $root);
        } catch (\Exception $e) {
            $this->assertEquals($expected, $e);
        }
    }

    public function dataInvoke(): array {
        return [
            '数値' => [
                'tokens' => [
                    new Token('NUMBER', '1'),
                ],
                'expected' => new NumberNode('1')
            ],
            '加算' => [
                'tokens' => [
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '1'),
                ],
                'expected' => new AdditiveNode(
                    new NumberNode('1'),
                    new NumberNode('1'),
                    '+'
                ),
            ],
            '加算減算 複合' => [
                'tokens' => [
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '2'),
                    new Token('OPERATOR', '-'),
                    new Token('NUMBER', '3'),
                ],
                'expected' => new AdditiveNode(
                    new AdditiveNode(
                        new NumberNode('1'),
                        new NumberNode('2'),
                        '+'
                    ),
                    new NumberNode('3'),
                    '-'
                ),
            ],
            '四則演算 複合' => [
                'tokens' => [
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '2'),
                    new Token('OPERATOR', '*'),
                    new Token('NUMBER', '3'),
                    new Token('OPERATOR', '-'),
                    new Token('NUMBER', '4'),
                ],
                'expected' => new AdditiveNode(
                    new AdditiveNode(
                        new NumberNode('1'),
                        new MultiplicativeNode(
                            new NumberNode('2'),
                            new NumberNode('3'),
                            '*'
                        ),
                        '+'
                    ),
                    new NumberNode('4'),
                    '-'
                ),
            ],
            'かっこ' => [
                'tokens' => [
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '2'),
                    new Token('OPERATOR', '*'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '3'),
                    new Token('OPERATOR', '-'),
                    new Token('NUMBER', '4'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new AdditiveNode(
                    new NumberNode('1'),
                    new MultiplicativeNode(
                        new NumberNode('2'),
                        new AdditiveNode(
                            new NumberNode('3'),
                            new NumberNode('4'),
                            '-'
                        ),
                        '*'
                    ),
                    '+'
                ),
            ],
            'Syntax Error' => [
                'tokens' => [
                    new Token('NUMBER', '1'),
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
                'expected' => new FunctionNode('hoge', [])
            ],
            '関数 引数' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '1'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new FunctionNode('hoge', [
                    new NumberNode('1')
                ])
            ],
            '関数 閉じ括弧なし' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '1'),
                ],
                'expected' => new \Exception('Syntax Error'),
            ],
            '関数 引数 複数' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '1'),
                    new Token('COMMA', ','),
                    new Token('NUMBER', '2'),
                    new Token('COMMA', ','),
                    new Token('NUMBER', '3'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new FunctionNode('hoge', [
                    new NumberNode('1'),
                    new NumberNode('2'),
                    new NumberNode('3'),
                ])
            ],
            '関数 引数が関数' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '1'),
                    new Token('COMMA', ','),
                    new Token('NUMBER', '2'),
                    new Token('COMMA', ','),
                    new Token('IDENT', 'fuga'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '1'),
                    new Token('R_PAREN', ')'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new FunctionNode('hoge', [
                    new NumberNode('1'),
                    new NumberNode('2'),
                    new FunctionNode('fuga', [
                        new NumberNode('1'),
                    ])
                ])
            ],
            '四則演算 + 関数' => [
                'tokens' => [
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '2'),
                    new Token('COMMA', ','),
                    new Token('NUMBER', '2'),
                    new Token('COMMA', ','),
                    new Token('IDENT', 'fuga'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '1'),
                    new Token('R_PAREN', ')'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new AdditiveNode(
                    new NumberNode('1'),
                    new FunctionNode('hoge', [
                        new AdditiveNode(
                            new NumberNode('1'),
                            new NumberNode('2'),
                            '+'
                        ),
                        new NumberNode('2'),
                        new FunctionNode('fuga', [
                            new NumberNode('1'),
                        ])
                    ]),
                    '+'
                )
            ],
      ];
    }
}