<?php
declare(strict_types=1);


namespace SDS\Tests\FloatMatrix;


use SDS\FloatMatrix;
use PHPUnit\Framework\TestCase;


class __constructTests extends TestCase
{
    /**
     * @covers \SDS\FloatMatrix::zeros
     * @covers \SDS\FloatMatrix::constant
     * @covers \SDS\Matrix::__construct
     * @covers \SDS\Matrix::setShape
     */
    public function test_constructor_determinism()
    {
        $this->assertTrue((FloatMatrix::zeros(1, 1))->equals(FloatMatrix::zeros(1, 1)));
        $this->assertTrue((FloatMatrix::zeros(1, 2))->equals(FloatMatrix::zeros(1, 2)));
        $this->assertTrue((FloatMatrix::zeros(2, 1))->equals(FloatMatrix::zeros(2, 1)));
        $this->assertTrue((FloatMatrix::zeros(2, 2))->equals(FloatMatrix::zeros(2, 2)));

        $this->assertFalse((FloatMatrix::zeros(1, 1))->equals(FloatMatrix::zeros(1, 2)));
        $this->assertFalse((FloatMatrix::zeros(1, 1))->equals(FloatMatrix::zeros(2, 1)));
        $this->assertFalse((FloatMatrix::zeros(1, 1))->equals(FloatMatrix::zeros(2, 2)));

        $this->assertFalse((FloatMatrix::zeros(1, 2))->equals(FloatMatrix::zeros(1, 1)));
        $this->assertFalse((FloatMatrix::zeros(1, 2))->equals(FloatMatrix::zeros(2, 1)));
        $this->assertFalse((FloatMatrix::zeros(1, 2))->equals(FloatMatrix::zeros(2, 2)));

        $this->assertFalse((FloatMatrix::zeros(2, 1))->equals(FloatMatrix::zeros(1, 1)));
        $this->assertFalse((FloatMatrix::zeros(2, 1))->equals(FloatMatrix::zeros(1, 2)));
        $this->assertFalse((FloatMatrix::zeros(2, 1))->equals(FloatMatrix::zeros(2, 2)));

        $this->assertFalse((FloatMatrix::zeros(2, 2))->equals(FloatMatrix::zeros(1, 1)));
        $this->assertFalse((FloatMatrix::zeros(2, 2))->equals(FloatMatrix::zeros(1, 2)));
        $this->assertFalse((FloatMatrix::zeros(2, 2))->equals(FloatMatrix::zeros(2, 1)));
    }

    /**
     * @covers \SDS\FloatMatrix::zeros
     * @covers \SDS\FloatMatrix::constant
     * @covers \SDS\Matrix::__construct
     *
     * @expectedException \DomainException
     */
    public function test_constructor_with_invalid_shape()
    {
        FloatMatrix::zeros(-2, 2);
    }

    /**
     * @covers \SDS\FloatMatrix::ones
     * @covers \SDS\FloatMatrix::constant
     * @covers \SDS\Matrix::__construct
     */
    public function test_ones()
    {
        $ones = FloatMatrix::ones(2, 2);

        $this->assertEquals(1, $ones[[0, 0]]);
        $this->assertEquals(1, $ones[[0, 1]]);
        $this->assertEquals(1, $ones[[1, 0]]);
        $this->assertEquals(1, $ones[[1, 1]]);
    }
}
