<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Lexer;
use Naotake51\Evaluation\Token;
use Naotake51\Evaluation\Nodes\Node;
use Naotake51\Evaluation\Nodes\NumberNode;
use Naotake51\Evaluation\Nodes\AdditiveNode;
use Naotake51\Evaluation\Nodes\MultiplicativeNode;

class LexerTest extends TestCase {
    /**
     * @return void
     * @dataProvider dataInvoke
     */
    public function testInvoke(array $tokens, Node $expected): void {
        $lexer = new Lexer();
        $root = $lexer($tokens);
        $this->assertEquals($expected, $root);
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
        ];
    }
}