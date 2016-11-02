<?php
declare(strict_types=1);


namespace SDS\Tests\IntMatrix;


use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class ArrayAccessTests extends TestCase
{
    /**
     * @covers \SDS\IntMatrix::offsetSet
     * @covers \SDS\IntMatrix::set
     * @covers \SDS\IntMatrix::offsetGet
     * @covers \SDS\IntMatrix::get
     */
    public function test_set_and_get_simplest_case()
    {
        $t = IntMatrix::zeros(2, 2);

        $t[[0, 0]] = 1;
        $t[[0, 1]] = 2;
        $t[[1, 0]] = 3;
        $t[[1, 1]] = 4;

        $this->assertEquals(1, $t[[0, 0]]);
        $this->assertEquals(2, $t[[0, 1]]);
        $this->assertEquals(3, $t[[1, 0]]);
        $this->assertEquals(4, $t[[1, 1]]);
    }

    /**
     * @covers \SDS\IntMatrix::offsetGet
     * @expectedException \OutOfRangeException
     */
    public function test_set_with_offset_with_too_many_dimensions()
    {
        $t = IntMatrix::zeros(4, 4);
        $t[[0, 0, 0]];
    }

    /**
     * @covers \SDS\IntMatrix::offsetSet
     * @expectedException \OutOfBoundsException
     */
    public function test_get_with_offset_with_too_big_coordinate_component()
    {
        $t = IntMatrix::zeros(4, 4);
        $t[[42, 42]];
    }

    /**
     * @covers \SDS\IntMatrix::offsetSet
     * @covers \SDS\IntMatrix::set
     *
     * @expectedException \TypeError
     */
    public function test_set_invalid_type()
    {
        $t = IntMatrix::zeros(4, 4);
        $t[[0, 0]] = 4.37;
    }
}
