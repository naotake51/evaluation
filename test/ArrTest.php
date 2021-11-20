<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Naotake51\Evaluation\Arr;

class ArrTest extends TestCase {
    /**
     * testMerge
     *
     * @param  Token[] $tokens
     * @param  Node|\Exception $expected
     * @return void
     *
     * @dataProvider dataMerge
     */
    public function testMerge($array, $merge, $expected): void {
        $result = Arr::merge($array, $merge);
        $this->assertSame($expected, $result);
    }

    public function dataMerge(): array {
        return [
            '追加' => [
                'array' => [
                    'a' => 'val a',
                    'b' => 'val b',
                    'c' => [
                        'c1' => 'val c1',
                        'c2' => 'val c2',
                        'c3' => 'val c3',
                    ],
                ],
                'merge' => [
                    'd' => 'val d',
                ],
                'expected' => [
                    'a' => 'val a',
                    'b' => 'val b',
                    'c' => [
                        'c1' => 'val c1',
                        'c2' => 'val c2',
                        'c3' => 'val c3',
                    ],
                    'd' => 'val d',
                ],
            ],
            '追加 子階層' => [
                'array' => [
                    'a' => 'val a',
                    'b' => 'val b',
                    'c' => [
                        'c1' => 'val c1',
                        'c2' => 'val c2',
                        'c3' => 'val c3',
                    ],
                ],
                'merge' => [
                    'c' => [
                        'c3' => 'merge c3',
                        'c4' => 'merge c4',
                    ]
                ],
                'expected' => [
                    'a' => 'val a',
                    'b' => 'val b',
                    'c' => [
                        'c1' => 'val c1',
                        'c2' => 'val c2',
                        'c3' => 'val c3',
                        'c4' => 'merge c4',
                    ],
                ],
            ],
            '追加 子階層 マージなし' => [
                'array' => [
                    'a' => 'val a',
                    'b' => 'val b',
                    'c' => [
                        'c1' => 'val c1',
                        'c2' => 'val c2',
                        'c3' => 'val c3',
                    ],
                ],
                'merge' => [
                    'c' => 'merge c3',
                ],
                'expected' => [
                    'a' => 'val a',
                    'b' => 'val b',
                    'c' => [
                        'c1' => 'val c1',
                        'c2' => 'val c2',
                        'c3' => 'val c3',
                    ],
                ],
            ],
        ];
    }
}