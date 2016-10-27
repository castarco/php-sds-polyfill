<?php
declare(strict_types=1);


namespace SDS\Tests\IntTensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class tileTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::tile
     * @covers \SDS\Tensor::pointerUpdater
     */
    public function test()
    {
        $t1 = IntTensor::fromArray([
            [1,   2,  3,  4],
            [5,   6,  7,  8],
            [9,  10, 11, 12],
            [13, 14, 15, 16]
        ]);
        $t2 = IntTensor::fromArray([
            [1,   2,  3,  4],
            [5,   6,  7,  8],
            [9,  10, 11, 12],
            [13, 14, 15, 16],
            [1,   2,  3,  4],
            [5,   6,  7,  8],
            [9,  10, 11, 12],
            [13, 14, 15, 16]
        ]);
        $t3 = IntTensor::fromArray([
            [ 1,  2,  3,  4,  1,  2,  3,  4],
            [ 5,  6,  7,  8,  5,  6,  7,  8],
            [ 9, 10, 11, 12,  9, 10, 11, 12],
            [13, 14, 15, 16, 13, 14, 15, 16]
        ]);

        $t4 = $t1->tile([2, 1]);
        $t5 = $t1->tile([1, 2]);

        $this->assertTrue($t2->equals($t4));
        $this->assertTrue($t4->equals($t2));

        $this->assertTrue($t3->equals($t5));
        $this->assertTrue($t5->equals($t3));

        $this->assertEquals(7, $t4[[5, 2]]);
        $this->assertEquals(9, $t5[[2, 4]]);
    }
}
