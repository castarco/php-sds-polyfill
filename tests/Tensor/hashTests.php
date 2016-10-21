<?php
declare(strict_types=1);


namespace SDS\Tests\FloatTensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class hashTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::hash
     */
    public function test_shape_equality()
    {
        $this->assertEquals((IntTensor::zeros([1]))->hash(), (IntTensor::zeros([1]))->hash());
        $this->assertEquals((IntTensor::zeros([2]))->hash(), (IntTensor::zeros([2]))->hash());
        $this->assertEquals((IntTensor::zeros([1, 1]))->hash(), (IntTensor::zeros([1, 1]))->hash());
        $this->assertEquals((IntTensor::zeros([2, 2]))->hash(), (IntTensor::zeros([2, 2]))->hash());
    }

    /**
     * @covers \SDS\Tensor::hash
     */
    public function test_inequality_after_setting_value()
    {
        $t1 = IntTensor::zeros([2, 2]);
        $t2 = IntTensor::zeros([2, 2]);

        $t1[[0, 0]] = 42;

        $this->assertNotEquals($t1->hash(), $t2->hash());
    }

    /**
     * @covers \SDS\Tensor::hash
     */
    public function test_equality_after_setting_value()
    {
        $t1 = IntTensor::zeros([2, 2]);
        $t2 = IntTensor::zeros([2, 2]);

        $t1[[0, 0]] = 42;
        $t2[[0, 0]] = 42;

        $this->assertEquals($t1->hash(), $t2->hash());
    }
}