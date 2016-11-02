<?php
declare(strict_types=1);


namespace SDS\Tests\IntMatrix;


use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class randomBinomialTests extends TestCase
{
    /**
     * @covers \SDS\IntMatrix::randomBinomial
     */
    public function test_bounds()
    {
        $rM = IntMatrix::randomBinomial(3, 3, 100);

        $this->assertGreaterThanOrEqual(0, $rM[[0, 0]]);
        $this->assertGreaterThanOrEqual(0, $rM[[0, 1]]);
        $this->assertGreaterThanOrEqual(0, $rM[[0, 2]]);
        $this->assertGreaterThanOrEqual(0, $rM[[1, 0]]);
        $this->assertGreaterThanOrEqual(0, $rM[[1, 1]]);
        $this->assertGreaterThanOrEqual(0, $rM[[1, 2]]);
        $this->assertGreaterThanOrEqual(0, $rM[[2, 0]]);
        $this->assertGreaterThanOrEqual(0, $rM[[2, 1]]);
        $this->assertGreaterThanOrEqual(0, $rM[[2, 2]]);

        $this->assertLessThanOrEqual(100, $rM[[0, 0]]);
        $this->assertLessThanOrEqual(100, $rM[[0, 1]]);
        $this->assertLessThanOrEqual(100, $rM[[0, 2]]);
        $this->assertLessThanOrEqual(100, $rM[[1, 0]]);
        $this->assertLessThanOrEqual(100, $rM[[1, 1]]);
        $this->assertLessThanOrEqual(100, $rM[[1, 2]]);
        $this->assertLessThanOrEqual(100, $rM[[2, 0]]);
        $this->assertLessThanOrEqual(100, $rM[[2, 1]]);
        $this->assertLessThanOrEqual(100, $rM[[2, 2]]);
    }

    /**
     * @covers \SDS\IntMatrix::randomBinomial
     */
    public function test_randomness()
    {
        $rM1 = IntMatrix::randomBinomial(3, 3, 1000);
        $rM2 = IntMatrix::randomBinomial(3, 3, 1000);

        $this->assertFalse($rM1->equals($rM2));
        $this->assertFalse($rM2->equals($rM1));
    }
}