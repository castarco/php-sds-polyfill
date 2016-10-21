<?php
declare(strict_types=1);


namespace SDS\Tests\IntTensor;


use SDS\FloatTensor;
use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class ArrayAccessTests extends TestCase
{
    /**
     * @covers \SDS\IntTensor::offsetSet
     * @covers \SDS\IntTensor::set
     * @covers \SDS\IntTensor::offsetGet
     * @covers \SDS\IntTensor::get
     * @covers \SDS\Tensor::getInternalIndex
     */
    public function test_set_and_get_simplest_case()
    {
        $t = IntTensor::zeros([2, 2]);

        $t[[0, 0]] = 1;
        $t[[0, 1]] = 2;
        $t[[1, 0]] = 3;
        $t[[1, 1]] = 4;

        $this->assertEquals(1, $t[[0, 0]]);
        $this->assertEquals(2, $t[[0, 1]]);
        $this->assertEquals(3, $t[[1, 0]]);
        $this->assertEquals(4, $t[[1, 1]]);
    }

    /**
     * @covers \SDS\IntTensor::offsetSet
     * @covers \SDS\IntTensor::set
     * @covers \SDS\IntTensor::offsetGet
     * @covers \SDS\IntTensor::get
     * @covers \SDS\Tensor::getInternalIndex
     */
    public function test_set_and_get_3_dims()
    {
        $t = IntTensor::zeros([2, 2, 2]);

        $t[[0, 0, 0]] = 1;
        $t[[0, 0, 1]] = 2;
        $t[[0, 1, 0]] = 3;
        $t[[0, 1, 1]] = 4;
        $t[[1, 0, 0]] = 5;
        $t[[1, 0, 1]] = 6;
        $t[[1, 1, 0]] = 7;
        $t[[1, 1, 1]] = 8;

        $this->assertEquals(1, $t[[0, 0, 0]]);
        $this->assertEquals(2, $t[[0, 0, 1]]);
        $this->assertEquals(3, $t[[0, 1, 0]]);
        $this->assertEquals(4, $t[[0, 1, 1]]);
        $this->assertEquals(5, $t[[1, 0, 0]]);
        $this->assertEquals(6, $t[[1, 0, 1]]);
        $this->assertEquals(7, $t[[1, 1, 0]]);
        $this->assertEquals(8, $t[[1, 1, 1]]);
    }

    /**
     * @covers \SDS\IntTensor::offsetGet
     * @covers \SDS\Tensor::getInternalIndex
     *
     * @expectedException \SDS\Exceptions\ShapeMismatchException
     * @expectedExceptionMessage Unexpected number of dimensions on the coordinates
     */
    public function test_set_with_offset_with_too_many_dimensions()
    {
        $t = IntTensor::zeros([4]);
        $t[[0, 0]];
    }

    /**
     * @covers \SDS\IntTensor::offsetSet
     * @covers \SDS\Tensor::getInternalIndex
     *
     * @expectedException \SDS\Exceptions\ShapeMismatchException
     * @expectedExceptionMessage The passed offset does not fit into the tensor's shape
     */
    public function test_get_with_offset_with_too_big_coordinate_component()
    {
        $t = IntTensor::zeros([4]);
        $t[[42]];
    }

    /**
     * @covers \SDS\IntTensor::offsetSet
     * @covers \SDS\IntTensor::set
     * @covers \SDS\Tensor::getInternalIndex
     *
     * @expectedException \TypeError
     */
    public function test_set_invalid_type()
    {
        $t = IntTensor::zeros([4]);
        $t[[0]] = 4.37;
    }
}
