<?php
declare(strict_types=1);


namespace SDS\Tests\IntMatrix;


use SDS\IntMatrix;
use PHPUnit\Framework\TestCase;


class __constructTests extends TestCase
{
    /**
     * @covers \SDS\IntMatrix::zeros
     * @covers \SDS\IntMatrix::constant
     * @covers \SDS\IntMatrix::initWithConstant
     * @covers \SDS\Matrix::__construct
     * @covers \SDS\Matrix::setShape
     */
    public function test_constructor_determinism()
    {
        $this->assertTrue((IntMatrix::zeros(1, 1))->equals(IntMatrix::zeros(1, 1)));
        $this->assertTrue((IntMatrix::zeros(1, 2))->equals(IntMatrix::zeros(1, 2)));
        $this->assertTrue((IntMatrix::zeros(2, 1))->equals(IntMatrix::zeros(2, 1)));
        $this->assertTrue((IntMatrix::zeros(2, 2))->equals(IntMatrix::zeros(2, 2)));

        $this->assertFalse((IntMatrix::zeros(1, 1))->equals(IntMatrix::zeros(1, 2)));
        $this->assertFalse((IntMatrix::zeros(1, 1))->equals(IntMatrix::zeros(2, 1)));
        $this->assertFalse((IntMatrix::zeros(1, 1))->equals(IntMatrix::zeros(2, 2)));

        $this->assertFalse((IntMatrix::zeros(1, 2))->equals(IntMatrix::zeros(1, 1)));
        $this->assertFalse((IntMatrix::zeros(1, 2))->equals(IntMatrix::zeros(2, 1)));
        $this->assertFalse((IntMatrix::zeros(1, 2))->equals(IntMatrix::zeros(2, 2)));

        $this->assertFalse((IntMatrix::zeros(2, 1))->equals(IntMatrix::zeros(1, 1)));
        $this->assertFalse((IntMatrix::zeros(2, 1))->equals(IntMatrix::zeros(1, 2)));
        $this->assertFalse((IntMatrix::zeros(2, 1))->equals(IntMatrix::zeros(2, 2)));

        $this->assertFalse((IntMatrix::zeros(2, 2))->equals(IntMatrix::zeros(1, 1)));
        $this->assertFalse((IntMatrix::zeros(2, 2))->equals(IntMatrix::zeros(1, 2)));
        $this->assertFalse((IntMatrix::zeros(2, 2))->equals(IntMatrix::zeros(2, 1)));
    }

    /**
     * @covers \SDS\IntMatrix::zeros
     * @covers \SDS\IntMatrix::constant
     * @covers \SDS\Matrix::__construct
     *
     * @expectedException \InvalidArgumentException
     */
    public function test_constructor_with_invalid_shape()
    {
        IntMatrix::zeros(-2, 2);
    }

    /**
     * @covers \SDS\IntMatrix::ones
     * @covers \SDS\IntMatrix::constant
     * @covers \SDS\Matrix::__construct
     */
    public function test_ones()
    {
        $ones = IntMatrix::ones(2, 2);

        $this->assertEquals(1, $ones[[0, 0]]);
        $this->assertEquals(1, $ones[[0, 1]]);
        $this->assertEquals(1, $ones[[1, 0]]);
        $this->assertEquals(1, $ones[[1, 1]]);
    }
}
