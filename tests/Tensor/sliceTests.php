<?php
declare(strict_types=1);


namespace SDS\Tests\FloatTensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class sliceTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::slice
     * @covers \SDS\Tensor::checkSliceSpec
     * @covers \SDS\Tensor::getNormalizedSliceSpec
     * @covers \SDS\Tensor::getShapeFromSliceSpec
     * @covers \SDS\Tensor::getInternalSlicesToBeCopied
     */
    public function test()
    {
        $t = IntTensor::zeros([3, 3]);
        $t[[0, 0]] = 1;
        $t[[0, 1]] = 2;
        $t[[0, 2]] = 3;
        $t[[1, 0]] = 4;
        $t[[1, 1]] = 5;
        $t[[1, 2]] = 6;
        $t[[2, 0]] = 7;
        $t[[2, 1]] = 8;
        $t[[2, 2]] = 9;

        $t2 = $t->slice([[0, 1], [0, 1]]);
        $this->assertEquals(1, $t2[[0, 0]]);
        $this->assertEquals(2, $t2[[0, 1]]);
        $this->assertEquals(4, $t2[[1, 0]]);
        $this->assertEquals(5, $t2[[1, 1]]);

        $t3 = $t->slice([[0, 1], [1, 2]]);
        $this->assertEquals(2, $t3[[0, 0]]);
        $this->assertEquals(3, $t3[[0, 1]]);
        $this->assertEquals(5, $t3[[1, 0]]);
        $this->assertEquals(6, $t3[[1, 1]]);

        $t4 = $t->slice([[1, 2], [0, 1]]);
        $this->assertEquals(4, $t4[[0, 0]]);
        $this->assertEquals(5, $t4[[0, 1]]);
        $this->assertEquals(7, $t4[[1, 0]]);
        $this->assertEquals(8, $t4[[1, 1]]);

        $t5 = $t->slice([[1, 2], [1, 2]]);
        $this->assertEquals(5, $t5[[0, 0]]);
        $this->assertEquals(6, $t5[[0, 1]]);
        $this->assertEquals(8, $t5[[1, 0]]);
        $this->assertEquals(9, $t5[[1, 1]]);
    }
}
