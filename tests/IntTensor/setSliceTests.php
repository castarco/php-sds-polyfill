<?php
declare(strict_types=1);


namespace SDS\Tests\IntTensor;


use SDS\FloatTensor;
use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class setSliceTests extends TestCase
{
    /**
     * @covers \SDS\IntTensor::setSlice
     */
    public function test_with_IntTensor()
    {
        $t1 = IntTensor::zeros([3, 3]);
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
     * @covers \SDS\IntTensor::setSlice
     * @expectedException \TypeError
     */
    public function test_with_FloatTensor()
    {
        $t1 = IntTensor::zeros([3, 3]);
        $t2 = FloatTensor::ones([2, 2]);

        $t1->setSlice($t2, [[1, 2], [1, 2]]);
    }

    /**
     * @covers \SDS\IntTensor::offsetSet
     * @covers \SDS\IntTensor::setSlice
     */
    public function test_with_ArrayAccess_idiom()
    {
        $t1 = IntTensor::zeros([3, 3]);
        $t2 = IntTensor::ones([2, 2]);

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
     * @covers \SDS\IntTensor::offsetSet
     * @covers \SDS\IntTensor::setArrayAsSlice
     * @covers \SDS\Tensor::inferShapeAndExtractData
     */
    public function test_with_ArrayAccess_idiom_and_array_param()
    {
        $t1 = IntTensor::zeros([3, 3]);

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
     * @covers \SDS\IntTensor::offsetSet
     * @covers \SDS\IntTensor::setArrayAsSlice
     * @covers \SDS\Tensor::inferShapeAndExtractData
     */
    public function test_with_ArrayAccess_idiom_and_flat_array_param()
    {
        $t1 = IntTensor::zeros([3, 3]);

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
