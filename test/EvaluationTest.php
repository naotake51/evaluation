<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Evaluation;
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
            'Syntax Error' => [
                'functions' => [],
                'expression' => '1 + ',
                'expected' => new \Exception('Syntax Error'),
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
                'expected' => new \Exception('Syntax Error'),
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
            'マジックメソッド オーバーライド' => [
                'functions' => [
                    '__add' => function ($arguments) {
                        return '__add(' . implode(',', $arguments) . ')';
                    }
                ],
                'expression' => '1 + 2',
                'expected' => '__add(1,2)'
            ],
        ];
    }
}