<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\FloatTensor;

use PHPUnit\Framework\TestCase;


class ArrayAccessTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::offsetExists
     * @covers \SDS\Tensor::_offsetExists
     */
    public function test_isset_on_invalid_offset()
    {
        $t = FloatTensor::zeros([4]);
        $this->assertFalse(isset($t[[5]]));
        $this->assertFalse(isset($t[[0, 0]]));
        $this->assertFalse(isset($t[0]));
        $this->assertFalse(isset($t['0']));
    }

    /**
     * @covers \SDS\Tensor::offsetExists
     * @covers \SDS\Tensor::_offsetExists
     */
    public function test_isset_on_valid_offset()
    {
        $t1 = FloatTensor::zeros([4]);

        $this->assertTrue(isset($t1[[0]]));
        $this->assertTrue(isset($t1[[1]]));
        $this->assertTrue(isset($t1[[2]]));
        $this->assertTrue(isset($t1[[3]]));

        $t2 = FloatTensor::zeros([2, 2]);

        $this->assertTrue(isset($t2[[0, 0]]));
        $this->assertTrue(isset($t2[[0, 1]]));
        $this->assertTrue(isset($t2[[1, 0]]));
        $this->assertTrue(isset($t2[[1, 1]]));
    }

    /**
     * @covers \SDS\Tensor::offsetUnset
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Not supported operation
     */
    public function test_unset()
    {
        $t1 = FloatTensor::zeros([4]);
        unset($t1[[0]]);
    }
}
