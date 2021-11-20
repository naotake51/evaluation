<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Evaluation;

class EvaluationTest extends TestCase {
    /**
     * testInvoke
     *
     * @param  Token[] $tokens
     * @param  Node|\Exception $expected
     * @return void
     *
     * @dataProvider dataInvoke
     */
    public function testInvoke(string $expression, $expected): void {
        $callback = function ($identify, $arguments) {
            return $identify . '(' . implode(',', $arguments) . ')';
        };

        try {
            $evaluation = new Evaluation($callback);
            $result = $evaluation($expression);
            $this->assertSame($expected, $result);
        } catch (\Exception $e) {
            $this->assertEquals($expected, $e);
        }
    }

    public function dataInvoke(): array {
        return [
            '整数' => [
                'expression' => '1',
                'expected' => 1
            ],
            '少数' => [
                'expression' => '1.0',
                'expected' => 1.0
            ],
            '文字列' => [
                'expression' => '"aaa"',
                'expected' => "aaa"
            ],
            'True' => [
                'expression' => 'True',
                'expected' => true
            ],
            '加算' => [
                'expression' => '1 + 1',
                'expected' => 2,
            ],
            '加算減算 複合' => [
                'expression' => '1 + 2 - 3',
                'expected' => 0,
            ],
            '四則演算 複合' => [
                'expression' => '1 + 2 * 3 - 4',
                'expected' => 3,
            ],
            'かっこ' => [
                'expression' => '1 + 2 * (3 - 4)',
                'expected' => -1,
            ],
            'Syntax Error' => [
                'expression' => '1 + ',
                'expected' => new \Exception('Syntax Error'),
            ],
            '関数' => [
                'expression' => 'hoge()',
                'expected' => 'hoge()'
            ],
            '関数 引数' => [
                'expression' => 'hoge(1)',
                'expected' => 'hoge(1)'
            ],
            '関数 閉じ括弧なし' => [
                'expression' => 'hoge(1',
                'expected' => new \Exception('Syntax Error'),
            ],
            '関数 引数 複数' => [
                'expression' => 'hoge(1, 2, 3)',
                'expected' => 'hoge(1,2,3)'
            ],
            '関数 引数が関数' => [
                'expression' => 'hoge(1, "aaa", fuga(True))',
                'expected' => 'hoge(1,aaa,fuga(1))'
            ],
        ];
    }
}