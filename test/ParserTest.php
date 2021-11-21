<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Parser;
use Naotake51\Evaluation\Token;
use Naotake51\Evaluation\Errors\SyntaxError;

class ParserTest extends TestCase {
    /**
     * @return void
     * @dataProvider dataInvoke
     */
    public function testInvoke(string $expression, $expected): void {
        try {
            $parser = new Parser();
            $tokens = $parser($expression);
            $this->assertEquals($expected, $tokens);
        } catch (\Exception $e) {
            $this->assertEquals($expected, $e);
        }
}

    public function dataInvoke(): array {
        return [
            '四則演算' => [
                'expression' => '1 + 1 - 1 * 1 / 1 % 1',
                'expected' => [
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '-'),
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '*'),
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '/'),
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '%'),
                    new Token('INTEGER', '1'),
                ]
            ],
            '数値' => [
                'expression' => '100 + 0.1 + .5 + 3000.0001',
                'expected' => [
                    new Token('INTEGER', '100'),
                    new Token('OPERATOR', '+'),
                    new Token('FLOAT', '0.1'),
                    new Token('OPERATOR', '+'),
                    new Token('FLOAT', '.5'),
                    new Token('OPERATOR', '+'),
                    new Token('FLOAT', '3000.0001'),
                ]
            ],
            'かっこ' => [
                'expression' => '1 + (1 - 2) * (4 / 5)',
                'expected' => [
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '+'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '1'),
                    new Token('OPERATOR', '-'),
                    new Token('INTEGER', '2'),
                    new Token('R_PAREN', ')'),
                    new Token('OPERATOR', '*'),
                    new Token('L_PAREN', '('),
                    new Token('INTEGER', '4'),
                    new Token('OPERATOR', '/'),
                    new Token('INTEGER', '5'),
                    new Token('R_PAREN', ')'),
                ]
            ],
            '識別子' => [
                'expression' => 'XXX + 1',
                'expected' => [
                    new Token('IDENT', 'XXX'),
                    new Token('OPERATOR', '+'),
                    new Token('INTEGER', '1'),
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
                'expected' => new SyntaxError('syntax error.')
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
                    new Token('INTEGER', '1'),
                    new Token('COMMA', ','),
                    new Token('STRING', '"2"'),
                    new Token('R_PAREN', ')'),
                    new Token('R_PAREN', ')'),
                ]
            ],
            '配列' => [
                'expression' => '[1, 2, 1 * 1 / 1]',
                'expected' => [
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
                ]
            ],
            'オブジェクト' => [
                'expression' => "{'a': 1, 'b': 2, 'c': {'c1': 1, 'c2': 2}}",
                'expected' => [
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
                ]
            ],
       ];
    }
}