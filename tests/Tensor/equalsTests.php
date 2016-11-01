<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\FloatTensor;
use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class equalsTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::equals
     */
    public function test_shape_equality()
    {
        $this->assertTrue((IntTensor::zeros([1]))->equals(IntTensor::zeros([1])));
        $this->assertTrue((IntTensor::zeros([2]))->equals(IntTensor::zeros([2])));
        $this->assertTrue((IntTensor::zeros([1, 1]))->equals(IntTensor::zeros([1, 1])));
        $this->assertTrue((IntTensor::zeros([2, 2]))->equals(IntTensor::zeros([2, 2])));
    }

    /**
     * @covers \SDS\Tensor::equals
     */
    public function test_shape_inequality()
    {
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
     * @covers \SDS\Tensor::equals
     */
    public function test_class_inequality()
    {
        $this->assertFalse((FloatTensor::zeros([1]))->equals(IntTensor::zeros([1])));
        $this->assertFalse((IntTensor::zeros([1]))->equals(FloatTensor::zeros([1])));
    }

    /**
     * @covers \SDS\Tensor::equals
     */
    public function test_inequality_after_setting_value()
    {
        $t1 = IntTensor::zeros([2, 2]);
        $t2 = IntTensor::zeros([2, 2]);

        $t1[[0, 0]] = 42;

        $this->assertFalse($t1->equals($t2));
        $this->assertFalse($t2->equals($t1));
    }

    /**
     * @covers \SDS\Tensor::equals
     */
    public function test_equality_after_setting_value()
    {
        $t1 = IntTensor::zeros([2, 2]);
        $t2 = IntTensor::zeros([2, 2]);

        $t1[[0, 0]] = 42;
        $t2[[0, 0]] = 42;

        $this->assertTrue($t1->equals($t2));
        $this->assertTrue($t2->equals($t1));
    }
}