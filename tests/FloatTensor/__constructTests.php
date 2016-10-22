<?php
declare(strict_types=1);


namespace SDS\Tests\FloatTensor;


use SDS\FloatTensor;
use PHPUnit\Framework\TestCase;


class __constructTests extends TestCase
{
    /**
     * @covers \SDS\FloatTensor::__construct
     * @covers \SDS\FloatTensor::initWithConstant
     * @covers \SDS\Tensor::__construct
     * @covers \SDS\Tensor::checkShape
     * @covers \SDS\Tensor::setShape
     */
    public function test_constructor_determinism()
    {
        $this->assertTrue((FloatTensor::zeros([1]))->equals(FloatTensor::zeros([1])));
        $this->assertTrue((FloatTensor::zeros([2]))->equals(FloatTensor::zeros([2])));
        $this->assertTrue((FloatTensor::zeros([1, 1]))->equals(FloatTensor::zeros([1, 1])));
        $this->assertTrue((FloatTensor::zeros([2, 2]))->equals(FloatTensor::zeros([2, 2])));

        $this->assertFalse((FloatTensor::zeros([1]))->equals(FloatTensor::zeros([2])));
        $this->assertFalse((FloatTensor::zeros([1]))->equals(FloatTensor::zeros([1, 1])));
        $this->assertFalse((FloatTensor::zeros([1]))->equals(FloatTensor::zeros([2, 2])));

        $this->assertFalse((FloatTensor::zeros([2]))->equals(FloatTensor::zeros([1])));
        $this->assertFalse((FloatTensor::zeros([2]))->equals(FloatTensor::zeros([1, 1])));
        $this->assertFalse((FloatTensor::zeros([2]))->equals(FloatTensor::zeros([2, 2])));

        $this->assertFalse((FloatTensor::zeros([1, 1]))->equals(FloatTensor::zeros([1])));
        $this->assertFalse((FloatTensor::zeros([1, 1]))->equals(FloatTensor::zeros([2])));
        $this->assertFalse((FloatTensor::zeros([1, 1]))->equals(FloatTensor::zeros([2, 2])));

        $this->assertFalse((FloatTensor::zeros([2, 2]))->equals(FloatTensor::zeros([1])));
        $this->assertFalse((FloatTensor::zeros([2, 2]))->equals(FloatTensor::zeros([2])));
        $this->assertFalse((FloatTensor::zeros([2, 2]))->equals(FloatTensor::zeros([1, 1])));
    }

    /**
     * @covers \SDS\FloatTensor::__construct
     * @covers \SDS\Tensor::__construct
     * @covers \SDS\Tensor::checkShape
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Shape dimensions must have a strictly positive width
     */
    public function test_constructor_with_invalid_shape()
    {
        FloatTensor::zeros([-2]);
    }
}
