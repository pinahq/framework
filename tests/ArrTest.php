<?php

use PHPUnit\Framework\TestCase;
use Pina\Arr;

class ArrTest extends TestCase
{

    public function testDiff()
    {
        $a1 = [1, 2, 2, 3];
        $a2 = [2, 3];
        $this->assertEquals([1, 2], Arr::diff($a1, $a2));

        $a1 = [];
        $a2 = [2, 3];
        $this->assertEquals([], Arr::diff($a1, $a2));

        $a1 = [1, 2, 2, 3];
        $a2 = [];
        $this->assertEquals([1, 2, 2, 3], Arr::diff($a1, $a2));

        $a1 = ["hello!", "hello!", 2, 2, 3];
        $a2 = ["hello!"];
        $this->assertEquals(["hello!", 2, 2, 3], Arr::diff($a1, $a2));
    }

    public function testColumn()
    {
        $a = [
            ['k1', 'v1'],
            ['k2', 'v2'],
            ['k3', 'v3'],
        ];

        $this->assertEquals(['k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3'], Arr::column($a, 1, 0));
    }

    public function testArrGet()
    {
        $a = [
            'name' => 'value',
            'key' => 'other-value',
            'deep' => [
                'deeper' => [
                    'deepest' => 'something',
                    'deepest2' => 'something2',
                ],
                'deeper2' => [
                    'deepest' => 'something3',
                    'deepest3' => 'something4',
                ]
            ]
        ];

        $this->assertEquals('value', Arr::get($a, 'name'));
        $this->assertEquals('something', Arr::get($a, 'deep.deeper.deepest'));
        $this->assertEquals(['deepest' => 'something', 'deepest2' => 'something2'], Arr::get($a, 'deep.deeper'));

        $this->assertEquals(['something', 'something3'], Arr::get($a, 'deep.*.deepest'));
        $this->assertEquals(['something', 'something2', 'something3', 'something4'], Arr::get($a, 'deep.*.*'));
    }

    public function testArrSet()
    {
        $a = [];

        Arr::set($a, '1.2.3', '123');

        $this->assertEquals(['1' => ['2' => ['3' => '123']]], $a);
    }

    public function testArrForget()
    {
        $a = [
            'name' => 'value',
            'key' => 'other-value',
            'deep' => [
                'deeper' => [
                    'deepest' => 'something',
                    'deepest2' => 'something2',
                ],
                'deeper2' => [
                    'deepest' => 'something3',
                    'deepest3' => 'something4',
                ]
            ]
        ];

        $b = $a;
        Arr::forget($b, 'deep');
        $this->assertEquals(['name' => 'value', 'key' => 'other-value'], $b);

        $b = $a;
        Arr::forget($b, ['deep', 'key']);
        $this->assertEquals(['name' => 'value'], $b);

        $b = $a;
        Arr::forget($b, 'deep.deeper2');
        $this->assertEquals([
            'name' => 'value',
            'key' => 'other-value',
            'deep' => [
                'deeper' => [
                    'deepest' => 'something',
                    'deepest2' => 'something2',
                ]
            ]
                ], $b);
    }

    public function testArrHas()
    {
        $a = [
            'name' => 'value',
            'key' => 'other-value',
            'deep' => [
                'deeper' => [
                    'deepest' => 'something',
                    'deepest2' => 'something2',
                ],
                'deeper2' => [
                    'deepest' => 'something3',
                    'deepest3' => 'something4',
                ]
            ]
        ];


        $this->assertEquals(true, Arr::has($a, 'name'));
        $this->assertEquals(false, Arr::has($a, 'not-name'));
        $this->assertEquals(true, Arr::has($a, 'deep.deeper'));
        $this->assertEquals(true, Arr::has($a, 'deep.deeper.deepest'));
        $this->assertEquals(false, Arr::has($a, 'deep.*.deepest'));
    }

    public function testArrOnly()
    {
        $a = [
            'name' => 'value',
            'key' => 'other-value',
            'deep' => [
                'deeper' => [
                    'deepest' => 'something',
                    'deepest2' => 'something2',
                ],
                'deeper2' => [
                    'deepest' => 'something3',
                    'deepest3' => 'something4',
                ]
            ]
        ];
        $b = Arr::only($a, ['key', 'deep']);
        $this->assertEquals([
            'key' => 'other-value',
            'deep' => [
                'deeper' => [
                    'deepest' => 'something',
                    'deepest2' => 'something2',
                ],
                'deeper2' => [
                    'deepest' => 'something3',
                    'deepest3' => 'something4',
                ]
            ]
        ], $b);
    }
    public function testArrPull()
    {
        $a = [
            'name' => 'value',
            'key' => 'other-value',
            'deep' => [
                'deeper' => [
                    'deepest' => 'something',
                    'deepest2' => 'something2',
                ],
                'deeper2' => [
                    'deepest' => 'something3',
                    'deepest3' => 'something4',
                ]
            ]
        ];
        $b = Arr::pull($a, 'deep');
        $this->assertEquals([
            'name' => 'value',
            'key' => 'other-value',
        ], $a);
        $this->assertEquals([
            'deeper' => [
                'deepest' => 'something',
                'deepest2' => 'something2',
            ],
            'deeper2' => [
                'deepest' => 'something3',
                'deepest3' => 'something4',
            ]
        ], $b);
    }

}
