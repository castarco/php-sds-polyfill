<?php
declare(strict_types=1);


namespace SDS\Tests\IntTensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class randomUniformTests extends TestCase
{
    /**
     * @covers \SDS\IntTensor::randomUniform
     */
    public function test_bounds()
    {
        $rT = IntTensor::randomUniform([3, 3], 1000000, 0);

        $this->assertGreaterThanOrEqual(0, $rT[[0, 0]]);
        $this->assertGreaterThanOrEqual(0, $rT[[0, 1]]);
        $this->assertGreaterThanOrEqual(0, $rT[[0, 2]]);
        $this->assertGreaterThanOrEqual(0, $rT[[1, 0]]);
        $this->assertGreaterThanOrEqual(0, $rT[[1, 1]]);
        $this->assertGreaterThanOrEqual(0, $rT[[1, 2]]);
        $this->assertGreaterThanOrEqual(0, $rT[[2, 0]]);
        $this->assertGreaterThanOrEqual(0, $rT[[2, 1]]);
        $this->assertGreaterThanOrEqual(0, $rT[[2, 2]]);

        $this->assertLessThanOrEqual(1000000, $rT[[0, 0]]);
        $this->assertLessThanOrEqual(1000000, $rT[[0, 1]]);
        $this->assertLessThanOrEqual(1000000, $rT[[0, 2]]);
        $this->assertLessThanOrEqual(1000000, $rT[[1, 0]]);
        $this->assertLessThanOrEqual(1000000, $rT[[1, 1]]);
        $this->assertLessThanOrEqual(1000000, $rT[[1, 2]]);
        $this->assertLessThanOrEqual(1000000, $rT[[2, 0]]);
        $this->assertLessThanOrEqual(1000000, $rT[[2, 1]]);
        $this->assertLessThanOrEqual(1000000, $rT[[2, 2]]);
    }

    /**
     * @covers \SDS\IntTensor::randomUniform
     */
    public function test_randomness()
    {
        $rT1 = IntTensor::randomUniform([3, 3], 1000000, 0);
        $rT2 = IntTensor::randomUniform([3, 3], 1000000, 0);

        $this->assertFalse($rT1->equals($rT2));
        $this->assertFalse($rT2->equals($rT1));
    }
}