<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Parser;
use Naotake51\Evaluation\Token;

class ParserTest extends TestCase {
    /**
     * @return void
     * @dataProvider dataInvoke
     */
    public function testInvoke(string $expression, $expected): void {
        $parser = new Parser();
        $tokens = $parser($expression);
        $this->assertEquals($expected, $tokens);
    }

    public function dataInvoke(): array {
        return [
            '四則演算' => [
                'expression' => '1 + 1 - 1 * 1 / 1 % 1',
                'expected' => [
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '-'),
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '*'),
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '/'),
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '%'),
                    new Token('NUMBER', '1'),
                ]
            ],
            '数値' => [
                'expression' => '100 + 0.1 + .5 + 3000.0001',
                'expected' => [
                    new Token('NUMBER', '100'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '0.1'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '.5'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '3000.0001'),
                ]
            ],
            'かっこ' => [
                'expression' => '1 + (1 - 2) * (4 / 5)',
                'expected' => [
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '1'),
                    new Token('OPERATOR', '-'),
                    new Token('NUMBER', '2'),
                    new Token('R_PAREN', ')'),
                    new Token('OPERATOR', '*'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '4'),
                    new Token('OPERATOR', '/'),
                    new Token('NUMBER', '5'),
                    new Token('R_PAREN', ')'),
                ]
            ],
            '識別子' => [
                'expression' => 'XXX + 1',
                'expected' => [
                    new Token('IDENT', 'XXX'),
                    new Token('OPERATOR', '+'),
                    new Token('NUMBER', '1'),
                ]
            ],
            '文字列' => [
                'expression' => '"abcdef" + \'xyz\'',
                'expected' => [
                    new Token('STRING', '"abcdef"'),
                    new Token('OPERATOR', '+'),
                    new Token('STRING', "'xyz'"),
                ]
            ],
            '文字列 エラー' => [
                'expression' => '"abcdef"aaa" + \'xyz\'',
                'expected' => false
            ],
            '関数' => [
                'expression' => 'xxx(aaa, "bbb", yyy(1, "2"))',
                'expected' => [
                    new Token('IDENT', 'xxx'),
                    new Token('L_PAREN', '('),
                    new Token('IDENT', 'aaa'),
                    new Token('COMMA', ','),
                    new Token('STRING', '"bbb"'),
                    new Token('COMMA', ','),
                    new Token('IDENT', 'yyy'),
                    new Token('L_PAREN', '('),
                    new Token('NUMBER', '1'),
                    new Token('COMMA', ','),
                    new Token('STRING', '"2"'),
                    new Token('R_PAREN', ')'),
                    new Token('R_PAREN', ')'),
                ]
            ],
        ];
    }
}