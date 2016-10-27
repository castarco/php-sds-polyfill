<?php
declare(strict_types=1);


namespace SDS\Tests\IntTensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class shapesTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::reshape
     * @covers \SDS\Tensor::getShape
     */
    public function test_reshape()
    {
        $t1 = IntTensor::fromArray([
            [1,   2,  3,  4],
            [5,   6,  7,  8],
            [9,  10, 11, 12],
            [13, 14, 15, 16]
        ]);
        $t2 = IntTensor::fromArray([
            [1,  2,  3,  4,  5,  6,  7,  8],
            [9, 10, 11, 12, 13, 14, 15, 16]
        ]);
        $t3 = IntTensor::fromArray([
            [1, 2],
            [3, 4],
            [5, 6],
            [7, 8],
            [9, 10],
            [11, 12],
            [13, 14],
            [15, 16]
        ]);

        $t4 = $t1->reshape([2, 8]);
        $t5 = $t1->reshape([8, 2]);

        $this->assertEquals([4, 4], $t1->getShape());
        $this->assertEquals([2, 8], $t4->getShape());
        $this->assertEquals([8, 2], $t5->getShape());

        $this->assertTrue($t2->equals($t4));
        $this->assertTrue($t4->equals($t2));
        $this->assertTrue($t3->equals($t5));
        $this->assertTrue($t5->equals($t3));

        $this->assertEquals(10, $t4[[1, 1]]);
        $this->assertEquals(10, $t5[[4, 1]]);
    }

    /**
     * @covers \SDS\Tensor::reshape
     * @expectedException \SDS\Exceptions\ShapeMismatchException
     */
    public function test_reshape_with_invalid_shape()
    {
        IntTensor::fromArray([
            [1,   2,  3,  4],
            [5,   6,  7,  8],
            [9,  10, 11, 12],
            [13, 14, 15, 16]
        ])->reshape([3, 5]);
    }

    /**
     * @covers \SDS\Tensor::squeeze
     * @covers \SDS\tensor::getShape
     */
    public function test_squeeze()
    {
        $t1 = IntTensor::fromArray([[
            [1,   2,  3,  4],
            [5,   6,  7,  8],
            [9,  10, 11, 12],
            [13, 14, 15, 16]
        ]]);
        $t2 = IntTensor::fromArray([
            [[1,   2,  3,  4]],
            [[5,   6,  7,  8]],
            [[9,  10, 11, 12]],
            [[13, 14, 15, 16]]
        ]);

        $t3 = $t1->squeeze();
        $t4 = $t2->squeeze();

        $this->assertEquals([1, 4, 4], $t1->getShape());
        $this->assertEquals([4, 1, 4], $t2->getShape());

        $this->assertEquals([4, 4], $t3->getShape());
        $this->assertEquals([4, 4], $t4->getShape());

        $this->assertEquals(10, $t3[[2, 1]]);
        $this->assertEquals(10, $t4[[2, 1]]);
    }

    /**
     *  @covers \SDS\Tensor::addDimension
     */
    public function test_addDimension()
    {
        $t1 = IntTensor::fromArray([
            [1,   2,  3,  4],
            [5,   6,  7,  8],
            [9,  10, 11, 12],
            [13, 14, 15, 16]
        ]);
        $t2 = IntTensor::fromArray([[
            [1,   2,  3,  4],
            [5,   6,  7,  8],
            [9,  10, 11, 12],
            [13, 14, 15, 16]
        ]]);
        $t3 = IntTensor::fromArray([
            [[1,   2,  3,  4]],
            [[5,   6,  7,  8]],
            [[9,  10, 11, 12]],
            [[13, 14, 15, 16]]
        ]);
        $t4 = IntTensor::fromArray([
            [[1],   [2],  [3],  [4]],
            [[5],   [6],  [7],  [8]],
            [[9],  [10], [11], [12]],
            [[13], [14], [15], [16]]
        ]);

        $this->assertTrue($t2->equals($t1->addDimension(0)));
        $this->assertTrue($t3->equals($t1->addDimension(1)));
        $this->assertTrue($t4->equals($t1->addDimension(2)));

        $this->assertEquals(10, $t2[[0, 2, 1]]);
        $this->assertEquals(10, $t3[[2, 0, 1]]);
        $this->assertEquals(10, $t4[[2, 1, 0]]);
    }
}
