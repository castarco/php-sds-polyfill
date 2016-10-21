<?php
declare(strict_types=1);


namespace SDS\Tests\FloatTensor;


use SDS\FloatTensor;

use PHPUnit\Framework\TestCase;


class ArrayAccessTests extends TestCase
{
    /**
     * @covers \SDS\FloatTensor::offsetSet
     * @covers \SDS\FloatTensor::set
     * @covers \SDS\FloatTensor::offsetGet
     * @covers \SDS\FloatTensor::get
     * @covers \SDS\Tensor::getInternalIndex
     */
    public function test_set_and_get_simplest_case()
    {
        $t = FloatTensor::zeros([2, 2]);

        $t[[0, 0]] = 1.5;
        $t[[0, 1]] = 2.5;
        $t[[1, 0]] = 3.5;
        $t[[1, 1]] = 4.5;

        $this->assertEquals(1.5, $t[[0, 0]]);
        $this->assertEquals(2.5, $t[[0, 1]]);
        $this->assertEquals(3.5, $t[[1, 0]]);
        $this->assertEquals(4.5, $t[[1, 1]]);
    }

    /**
     * @covers \SDS\FloatTensor::offsetSet
     * @covers \SDS\FloatTensor::set
     * @covers \SDS\FloatTensor::offsetGet
     * @covers \SDS\FloatTensor::get
     * @covers \SDS\Tensor::getInternalIndex
     */
    public function test_set_and_get_3_dims()
    {
        $t = FloatTensor::zeros([2, 2, 2]);

        $t[[0, 0, 0]] = 1.5;
        $t[[0, 0, 1]] = 2.5;
        $t[[0, 1, 0]] = 3.5;
        $t[[0, 1, 1]] = 4.5;
        $t[[1, 0, 0]] = 5.5;
        $t[[1, 0, 1]] = 6.5;
        $t[[1, 1, 0]] = 7.5;
        $t[[1, 1, 1]] = 8.5;

        $this->assertEquals(1.5, $t[[0, 0, 0]]);
        $this->assertEquals(2.5, $t[[0, 0, 1]]);
        $this->assertEquals(3.5, $t[[0, 1, 0]]);
        $this->assertEquals(4.5, $t[[0, 1, 1]]);
        $this->assertEquals(5.5, $t[[1, 0, 0]]);
        $this->assertEquals(6.5, $t[[1, 0, 1]]);
        $this->assertEquals(7.5, $t[[1, 1, 0]]);
        $this->assertEquals(8.5, $t[[1, 1, 1]]);
    }

    /**
     * @covers \SDS\FloatTensor::offsetGet
     * @covers \SDS\FloatTensor::get
     * @covers \SDS\Tensor::getInternalIndex
     *
     * @expectedException \SDS\Exceptions\ShapeMismatchException
     * @expectedExceptionMessage Unexpected number of dimensions on the coordinates
     */
    public function test_set_with_offset_with_too_many_dimensions()
    {
        $t = FloatTensor::zeros([4]);
        $t[[0, 0]];
    }

    /**
     * @covers \SDS\FloatTensor::offsetGet
     * @covers \SDS\FloatTensor::get
     * @covers \SDS\Tensor::getInternalIndex
     *
     * @expectedException \SDS\Exceptions\ShapeMismatchException
     * @expectedExceptionMessage The passed offset does not fit into the tensor's shape
     */
    public function test_get_with_offset_with_too_big_coordinate_component()
    {
        $t = FloatTensor::zeros([4]);
        $t[[42]];
    }

    /**
     * @covers \SDS\FloatTensor::offsetSet
     * @covers \SDS\FloatTensor::set
     * @covers \SDS\Tensor::getInternalIndex
     *
     * @expectedException \TypeError
     */
    public function test_set_invalid_type_on_FloatTensor()
    {
        $t = FloatTensor::zeros([4]);
        $t[[0]] = '4.37';
    }
}
