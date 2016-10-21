<?php
declare(strict_types=1);


namespace SDS\Tests\IntTensor;


use SDS\IntTensor;
use PHPUnit\Framework\TestCase;


class __constructTests extends TestCase
{
    /**
     * @covers \SDS\FloatTensor::__construct
     * @covers \SDS\Tensor::__construct
     * @covers \SDS\IntTensor::initWithConstant
     */
    public function test_constructor_determinism()
    {
        $this->assertTrue((IntTensor::zeros([1]))->equals(IntTensor::zeros([1])));
        $this->assertTrue((IntTensor::zeros([2]))->equals(IntTensor::zeros([2])));
        $this->assertTrue((IntTensor::zeros([1, 1]))->equals(IntTensor::zeros([1, 1])));
        $this->assertTrue((IntTensor::zeros([2, 2]))->equals(IntTensor::zeros([2, 2])));

        $this->assertFalse((IntTensor::zeros([1]))->equals(IntTensor::zeros([2])));
        $this->assertFalse((IntTensor::zeros([1]))->equals(IntTensor::zeros([1, 1])));
        $this->assertFalse((IntTensor::zeros([1]))->equals(IntTensor::zeros([2, 2])));

        $this->assertFalse((IntTensor::zeros([2]))->equals(IntTensor::zeros([1])));
        $this->assertFalse((IntTensor::zeros([2]))->equals(IntTensor::zeros([1, 1])));
        $this->assertFalse((IntTensor::zeros([2]))->equals(IntTensor::zeros([2, 2])));

        $this->assertFalse((IntTensor::zeros([1, 1]))->equals(IntTensor::zeros([1])));
        $this->assertFalse((IntTensor::zeros([1, 1]))->equals(IntTensor::zeros([2])));
        $this->assertFalse((IntTensor::zeros([1, 1]))->equals(IntTensor::zeros([2, 2])));

        $this->assertFalse((IntTensor::zeros([2, 2]))->equals(IntTensor::zeros([1])));
        $this->assertFalse((IntTensor::zeros([2, 2]))->equals(IntTensor::zeros([2])));
        $this->assertFalse((IntTensor::zeros([2, 2]))->equals(IntTensor::zeros([1, 1])));
    }

    /**
     * @covers \SDS\IntTensor::__construct
     * @covers \SDS\Tensor::__construct
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Shape dimensions must have a strictly positive width
     */
    public function test_constructor_with_invalid_shape()
    {
        IntTensor::zeros([2, -3]);
    }
}
