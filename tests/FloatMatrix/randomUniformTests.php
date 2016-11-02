<?php
declare(strict_types=1);


namespace SDS\Tests\FloatMatrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class randomUniformTests extends TestCase
{
    /**
     * @covers \SDS\FloatMatrix::randomUniform
     */
    public function test_bounds()
    {
        $rT = FloatMatrix::randomUniform(3, 3, 10, 0);

        $this->assertGreaterThanOrEqual(0, $rT[[0, 0]]);
        $this->assertGreaterThanOrEqual(0, $rT[[0, 1]]);
        $this->assertGreaterThanOrEqual(0, $rT[[0, 2]]);
        $this->assertGreaterThanOrEqual(0, $rT[[1, 0]]);
        $this->assertGreaterThanOrEqual(0, $rT[[1, 1]]);
        $this->assertGreaterThanOrEqual(0, $rT[[1, 2]]);
        $this->assertGreaterThanOrEqual(0, $rT[[2, 0]]);
        $this->assertGreaterThanOrEqual(0, $rT[[2, 1]]);
        $this->assertGreaterThanOrEqual(0, $rT[[2, 2]]);

        $this->assertLessThanOrEqual(10, $rT[[0, 0]]);
        $this->assertLessThanOrEqual(10, $rT[[0, 1]]);
        $this->assertLessThanOrEqual(10, $rT[[0, 2]]);
        $this->assertLessThanOrEqual(10, $rT[[1, 0]]);
        $this->assertLessThanOrEqual(10, $rT[[1, 1]]);
        $this->assertLessThanOrEqual(10, $rT[[1, 2]]);
        $this->assertLessThanOrEqual(10, $rT[[2, 0]]);
        $this->assertLessThanOrEqual(10, $rT[[2, 1]]);
        $this->assertLessThanOrEqual(10, $rT[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatMatrix::randomUniform
     */
    public function test_randomness()
    {
        $rT1 = FloatMatrix::randomUniform(3, 3, 10, 0);
        $rT2 = FloatMatrix::randomUniform(3, 3, 10, 0);

        $this->assertFalse($rT1->equals($rT2));
        $this->assertFalse($rT2->equals($rT1));
    }
}