<?php

declare(strict_types=1);

use Baka\Support\Arr;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public function testAll()
    {
        $this->assertTrue(
            Arr::all([2, 3, 4, 5], function ($item) {
                return $item > 1;
            })
        );
    }

    public function testAny()
    {
        $this->assertTrue(
            Arr::any([1, 2, 3, 4], function ($item) {
                return $item < 2;
            })
        );
    }

    public function testFlatten()
    {
        $this->assertSame(
            [1, 2, 3, 4],
            Arr::flatten([1, [2], 3, 4])
        );
    }

    public function testDeepFlatten()
    {
        $this->assertSame(
            [1, 2, 3, 4, 5],
            Arr::deepFlatten([1, [2], [[3], 4], 5])
        );
    }

    public function testDrop()
    {
        $this->assertSame(
            [2, 3],
            Arr::drop([1, 2, 3])
        );

        $this->assertSame(
            [3],
            Arr::drop([1, 2, 3], 2)
        );
    }

    public function testFindLast()
    {
        $this->assertSame(
            3,
            Arr::findLast([1, 2, 3, 4], function ($n) {
                return 1 === ($n % 2);
            })
        );
    }

    public function testFindLastIndex()
    {
        $this->assertSame(
            2,
            Arr::findLastIndex([1, 2, 3, 4], function ($n) {
                return 1 === ($n % 2);
            })
        );
    }

    public function testHead()
    {
        $this->assertSame(
            1,
            Arr::head([1, 2, 3])
        );
    }

    public function testTail()
    {
        $this->assertSame(
            [2, 3],
            Arr::tail([1, 2, 3])
        );

        $this->assertSame(
            [3],
            Arr::tail([3])
        );
    }

    public function testLast()
    {
        $this->assertSame(
            3,
            Arr::last([1, 2, 3])
        );
    }

    public function testPull()
    {
        $items = ['a', 'b', 'c', 'a', 'b', 'c'];
        Arr::pull($items, 'a', 'c');
        $this->assertSame(
            $items,
            ['b', 'b']
        );
    }

    public function testPluck()
    {
        $this->assertSame(
            ['Desk', 'Chair'],
            Arr::pluck([
                ['product_id' => 'prod-100', 'name' => 'Desk'],
                ['product_id' => 'prod-200', 'name' => 'Chair'],
            ], 'name')
        );
    }

    public function testReject()
    {
        $this->assertSame(
            ['Pear', 'Kiwi'],
            Arr::reject(['Apple', 'Pear', 'Kiwi', 'Banana'], function ($item) {
                return mb_strlen($item) > 4;
            })
        );
    }

    public function testRemove()
    {
        $this->assertSame(
            [0 => 1, 2 => 3],
            Arr::remove([1, 2, 3, 4], function ($n) {
                return 0 === ($n % 2);
            })
        );
    }

    public function testTake()
    {
        $this->assertSame(
            [1, 2, 3],
            Arr::take([1, 2, 3], 5)
        );

        $this->assertSame(
            [1, 2],
            Arr::take([1, 2, 3, 4, 5], 2)
        );
    }

    public function testWithout()
    {
        $this->assertSame(
            [3],
            Arr::without([2, 1, 2, 3], 1, 2)
        );
    }

    public function testHasDuplicates()
    {
        $this->assertTrue(
            Arr::hasDuplicates([1, 2, 3, 4, 5, 5])
        );
    }

    public function testGroupBy()
    {
        $this->assertSame(
            [
                34 => [
                    [
                        'name' => 'Mashrafe',
                        'age' => 34,
                    ],
                ],
                31 => [
                    [
                        'name' => 'Sakib',
                        'age' => 31,
                    ],
                ],
                29 => [
                    [
                        'name' => 'Tamim',
                        'age' => 29,
                    ],
                ],
            ],
            Arr::groupBy(
                [
                    ['name' => 'Mashrafe', 'age' => 34],
                    ['name' => 'Sakib', 'age' => 31],
                    ['name' => 'Tamim', 'age' => 29],
                ],
                'age'
            )
        );

        $this->assertSame(
            [3 => ['one', 'two'], 5 => ['three']],
            Arr::groupBy(['one', 'two', 'three'], 'strlen')
        );

        $peterClass = new \stdClass();
        $peterClass->name = 'Peter';
        $peterClass->age = '25';

        $appzcoderClass = new \stdClass();
        $appzcoderClass->name = 'Appzcoder';
        $appzcoderClass->age = '25';

        $this->assertSame([
            'Peter' => [$peterClass],
            'Appzcoder' => [$appzcoderClass],
        ], Arr::groupBy([
            'person' => $peterClass,
            'organization' => $appzcoderClass,
        ], 'name'));
    }

    public function testOrderBy()
    {
        $this->assertSame(
            [
                ['id' => 3, 'name' => 'Khaja'],
                ['id' => 2, 'name' => 'Joy'],
                ['id' => 1, 'name' => 'Raja'],
            ],
            Arr::orderBy(
                [
                    ['id' => 2, 'name' => 'Joy'],
                    ['id' => 3, 'name' => 'Khaja'],
                    ['id' => 1, 'name' => 'Raja'],
                ],
                'id',
                'desc'
            )
        );
    }
}
