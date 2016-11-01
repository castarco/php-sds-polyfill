<?php
declare(strict_types=1);


namespace SDS\Tests\IntMatrix;


use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class fromArrayTests extends TestCase
{
    /**
     * @covers \SDS\IntMatrix::fromArray
     */
    public function test_with_forced_shape()
    {
        $t = IntMatrix::fromArray([1, 2, 3, 4, 5, 6, 7, 8, 9], 3, 3);

        $this->assertEquals(1, $t[[0, 0]]);
        $this->assertEquals(2, $t[[0, 1]]);
        $this->assertEquals(3, $t[[0, 2]]);
        $this->assertEquals(4, $t[[1, 0]]);
        $this->assertEquals(5, $t[[1, 1]]);
        $this->assertEquals(6, $t[[1, 2]]);
        $this->assertEquals(7, $t[[2, 0]]);
        $this->assertEquals(8, $t[[2, 1]]);
        $this->assertEquals(9, $t[[2, 2]]);
    }

    /**
     * @covers \SDS\IntMatrix::fromArray
     */
    public function test_with_inferred_shape()
    {
        $t = IntMatrix::fromArray([[1, 2, 3], [4, 5, 6], [7, 8, 9]]);

        $this->assertEquals(1, $t[[0, 0]]);
        $this->assertEquals(2, $t[[0, 1]]);
        $this->assertEquals(3, $t[[0, 2]]);
        $this->assertEquals(4, $t[[1, 0]]);
        $this->assertEquals(5, $t[[1, 1]]);
        $this->assertEquals(6, $t[[1, 2]]);
        $this->assertEquals(7, $t[[2, 0]]);
        $this->assertEquals(8, $t[[2, 1]]);
        $this->assertEquals(9, $t[[2, 2]]);
    }

    /**
     * @covers \SDS\IntMatrix::fromArray
     *
     * @expectedException \InvalidArgumentException
     */
    public function test_with_forced_invalid_shape()
    {
        IntMatrix::fromArray([1, 2, 3, 4, 5, 6, 7, 8, 9], 3, 4);
    }

    /**
     * @covers \SDS\IntMatrix::fromArray
     *
     * @expectedException \InvalidArgumentException
     */
    public function test_with_inferred_shape_and_irregular_structure()
    {
        IntMatrix::fromArray([[1, 2, 3], [4, 5, 6, 7], [8, 9]]);
    }
}
