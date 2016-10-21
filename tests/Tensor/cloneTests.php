<?php
declare(strict_types=1);


namespace SDS\Tests\FloatTensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class cloneTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::__clone
     */
    public function test_equality()
    {
        $t1 = IntTensor::zeros([2, 2]);
        $t1[[0, 0]] = 1;
        $t1[[0, 1]] = 2;
        $t1[[1, 0]] = 3;
        $t1[[1, 1]] = 4;

        $t2 = clone $t1;

        $this->assertTrue($t1->equals($t2));
        $this->assertTrue($t2->equals($t1));
    }

    /**
     * @covers \SDS\Tensor::__clone
     */
    public function test_source_independence()
    {
        $t1 = IntTensor::zeros([2, 2]);
        $t1[[0, 0]] = 1;
        $t1[[0, 1]] = 2;
        $t1[[1, 0]] = 3;
        $t1[[1, 1]] = 4;

        $t2 = clone $t1;

        $t1[[0, 0]] = 41;
        $t1[[0, 1]] = 42;
        $t1[[1, 0]] = 43;
        $t1[[1, 1]] = 44;

        $this->assertEquals(1, $t2[[0, 0]]);
        $this->assertEquals(2, $t2[[0, 1]]);
        $this->assertEquals(3, $t2[[1, 0]]);
        $this->assertEquals(4, $t2[[1, 1]]);
    }

    /**
     * @covers \SDS\Tensor::__clone
     */
    public function test_destination_independence()
    {
        $t1 = IntTensor::zeros([2, 2]);
        $t1[[0, 0]] = 1;
        $t1[[0, 1]] = 2;
        $t1[[1, 0]] = 3;
        $t1[[1, 1]] = 4;

        $t2 = clone $t1;

        $t2[[0, 0]] = 41;
        $t2[[0, 1]] = 42;
        $t2[[1, 0]] = 43;
        $t2[[1, 1]] = 44;

        $this->assertEquals(1, $t1[[0, 0]]);
        $this->assertEquals(2, $t1[[0, 1]]);
        $this->assertEquals(3, $t1[[1, 0]]);
        $this->assertEquals(4, $t1[[1, 1]]);
    }
}
