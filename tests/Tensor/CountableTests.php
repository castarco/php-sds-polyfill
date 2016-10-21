<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\FloatTensor;

use PHPUnit\Framework\TestCase;


class CountableTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::count
     */
    public function test()
    {
        $this->assertEquals(4, count(FloatTensor::zeros([4])));
        $this->assertEquals(23, count(FloatTensor::zeros([23])));

        $this->assertEquals(12, count(FloatTensor::zeros([4, 3])));
        $this->assertEquals(12, count(FloatTensor::zeros([3, 4])));

        $this->assertEquals(20, count(FloatTensor::zeros([4, 5])));
        $this->assertEquals(20, count(FloatTensor::zeros([5, 4])));

        $this->assertEquals(105, count(FloatTensor::zeros([7, 3, 5])));
    }
}
