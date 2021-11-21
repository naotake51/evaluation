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
use Naotake51\Evaluation\Nodes\ArrayNode;
use Naotake51\Evaluation\Nodes\ObjectNode;
use Naotake51\Evaluation\Nodes\FunctionNode;
use Naotake51\Evaluation\Errors\SyntaxError;

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
                'expected' => new FunctionNode(
                    '__add',
                    [
                        new IntegerNode('1'),
                        new IntegerNode('1'),
                    ]
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
                'expected' => new FunctionNode(
                    '__sub',
                    [
                        new FunctionNode(
                            '__add',
                            [
                                new IntegerNode('1'),
                                new IntegerNode('2'),
                            ],
                        ),
                        new IntegerNode('3'),
                    ]
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
                'expected' => new FunctionNode(
                    '__sub',
                    [
                        new FunctionNode(
                            '__add',
                            [
                                new IntegerNode('1'),
                                new FunctionNode(
                                    '__mul',
                                    [
                                        new IntegerNode('2'),
                                        new IntegerNode('3'),
                                    ],
                                ),
                            ]
                        ),
                        new IntegerNode('4'),
                    ]
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
                'expected' => new FunctionNode(
                    '__add',
                    [
                        new IntegerNode('1'),
                        new FunctionNode(
                            '__mul',
                            [
                                new IntegerNode('2'),
                                new FunctionNode(
                                    '__sub',
                                    [
                                        new IntegerNode('3'),
                                        new IntegerNode('4'),
                                    ]
                                ),
                            ]
                        ),
                    ]
                ),
            ],
            'syntax error' => [
                'tokens' => [
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                ],
                'expected' => new SyntaxError('syntax error.'),
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
                    new Token('INTEGER', '1'),
                    new Token('R_PAREN', ')'),
                ],
                'expected' => new FunctionNode(
                    'hoge',
                    [
                        new IntegerNode('1')
                    ]
                )
            ],
            '関数 閉じ括弧なし' => [
                'tokens' => [
                    new Token('IDENT', 'hoge'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '1'),
                ],
                'expected' => new SyntaxError('syntax error.'),
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
                    ]
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
                            ]
                        )
                    ]
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
                'expected' => new FunctionNode(
                    '__add',
                    [
                        new IntegerNode('1'),
                        new FunctionNode(
                            'hoge',
                            [
                                new FunctionNode(
                                    '__add',
                                    [
                                        new IntegerNode('1'),
                                        new IntegerNode('2'),
                                    ]
                                ),
                                new IntegerNode('2'),
                                new FunctionNode(
                                    'fuga',
                                    [
                                        new IntegerNode('1'),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ],
            '配列' => [
                'tokens' => [
                    new Token('L_BRACKET', '['),
                    new Token('INTEGER', '1'),
                    new Token('COMMA', ','),
                    new Token('INTEGER', '2'),
                    new Token('COMMA', ','),
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '*'),
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '/'),
                    new Token('INTEGER', '1'),
                    new Token('R_BRACKET', ']'),
                ],
                'expected' => new ArrayNode([
                    new IntegerNode('1'),
                    new IntegerNode('2'),
                    new FunctionNode(
                        '__div',
                        [
                            new FunctionNode(
                                '__mul',
                                [
                                    new IntegerNode('1'),
                                    new IntegerNode('1'),
                                ]
                            ),
                            new IntegerNode('1'),
                        ]
                    ),
                ])
            ],
            'オブジェクト' => [
                'tokens' => [
                    new Token('L_BRACE', '{'),
                    new Token('STRING', "'a'"),
                    new Token('COLON', ':'),
                    new Token('INTEGER', '1'),
                    new Token('COMMA', ','),
                    new Token('STRING', "'b'"),
                    new Token('COLON', ':'),
                    new Token('INTEGER', '2'),
                    new Token('COMMA', ','),
                    new Token('STRING', "'c'"),
                    new Token('COLON', ':'),
                    new Token('L_BRACE', '{'),
                    new Token('STRING', "'c1'"),
                    new Token('COLON', ':'),
                    new Token('INTEGER', '1'),
                    new Token('COMMA', ','),
                    new Token('STRING', "'c2'"),
                    new Token('COLON', ':'),
                    new Token('INTEGER', '2'),
                    new Token('R_BRACE', '}'),
                    new Token('R_BRACE', '}'),
                ],
                'expected' => new ObjectNode([
                    ['key' => new StringNode("'a'"), 'value' => new IntegerNode('1')],
                    ['key' => new StringNode("'b'"), 'value' => new IntegerNode('2')],
                    ['key' => new StringNode("'c'"), 'value' => new ObjectNode([
                        ['key' => new StringNode("'c1'"), 'value' => new IntegerNode('1')],
                        ['key' => new StringNode("'c2'"), 'value' => new IntegerNode('2')],
                    ])],
                ])
            ],
        ];
    }
}