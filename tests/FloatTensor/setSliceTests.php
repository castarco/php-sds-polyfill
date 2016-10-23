<?php
declare(strict_types=1);


namespace SDS\Tests\FloatTensor;


use SDS\FloatTensor;
use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class setSliceTests extends TestCase
{
    /**
     * @covers \SDS\FloatTensor::setSlice
     */
    public function test_with_FloatTensor()
    {
        $t1 = FloatTensor::zeros([3, 3]);
        $t2 = FloatTensor::ones([2, 2]);

        $t1->setSlice($t2, [[1, 2], [1, 2]]);

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatTensor::setSlice
     */
    public function test_with_IntTensor()
    {
        $t1 = FloatTensor::zeros([3, 3]);
        $t2 = IntTensor::ones([2, 2]);

        $t1->setSlice($t2, [[1, 2], [1, 2]]);

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatTensor::offsetSet
     * @covers \SDS\FloatTensor::setSlice
     */
    public function test_with_ArrayAccess_idiom()
    {
        $t1 = FloatTensor::zeros([3, 3]);
        $t2 = FloatTensor::ones([2, 2]);

        $t1[[[1, 2], [1, 2]]] = $t2;

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatTensor::offsetSet
     * @covers \SDS\FloatTensor::setSlice
     */
    public function test_with_ArrayAccess_idiom_and_array_param()
    {
        $t1 = FloatTensor::zeros([3, 3]);

        $t1[[[1, 2], [1, 2]]] = [[1, 1], [1, 1]];

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatTensor::offsetSet
     * @covers \SDS\FloatTensor::setSlice
     */
    public function test_with_ArrayAccess_idiom_and_flat_array_param()
    {
        $t1 = FloatTensor::zeros([3, 3]);

        $t1[[[1, 2], [1, 2]]] = [1, 1, 1, 1];

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }
}
