<?php
declare(strict_types=1);


namespace SDS\Tests\Matrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class CountableTests extends TestCase
{
    /**
     * @covers \SDS\Matrix::count
     */
    public function test()
    {
        $this->assertEquals(4, count(FloatMatrix::zeros(4, 1)));
        $this->assertEquals(4, count(FloatMatrix::zeros(1, 4)));

        $this->assertEquals(23, count(FloatMatrix::zeros(1, 23)));
        $this->assertEquals(23, count(FloatMatrix::zeros(23, 1)));

        $this->assertEquals(12, count(FloatMatrix::zeros(4, 3)));
        $this->assertEquals(12, count(FloatMatrix::zeros(3, 4)));

        $this->assertEquals(20, count(FloatMatrix::zeros(4, 5)));
        $this->assertEquals(20, count(FloatMatrix::zeros(5, 4)));
    }
}
