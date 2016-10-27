<?php
declare(strict_types=1);


namespace SDS\Tests\IntTensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class transposeTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::checkPermutation
     * @covers \SDS\Tensor::transpose
     * @covers \SDS\Tensor::permute
     * @covers \SDS\Tensor::pointerUpdater
     */
    public function test()
    {
        $t1 = IntTensor::fromArray([
            [1,   2,  3,  4],
            [5,   6,  7,  8],
            [9,  10, 11, 12]
        ]);
        $t2 = IntTensor::fromArray([
            [1, 5, 9],
            [2, 6, 10],
            [3, 7, 11],
            [4, 8, 12]
        ]);

        $t3 = $t1->transpose([1, 0]);

        $this->assertEquals([4, 3], $t3->getShape());
        $this->assertTrue($t2->equals($t3));
        $this->assertTrue($t3->equals($t2));

        $this->assertEquals(12, $t3[[3, 2]]);
    }
}
