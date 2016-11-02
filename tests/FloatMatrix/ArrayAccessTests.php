<?php
declare(strict_types=1);


namespace SDS\Tests\FloatMatrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class ArrayAccessTests extends TestCase
{
    /**
     * @covers \SDS\FloatMatrix::offsetSet
     * @covers \SDS\FloatMatrix::set
     * @covers \SDS\FloatMatrix::offsetGet
     * @covers \SDS\FloatMatrix::get
     */
    public function test_set_and_get_simplest_case()
    {
        $t = FloatMatrix::zeros(2, 2);

        $t[[0, 0]] = 1.5;
        $t[[0, 1]] = 2.5;
        $t[[1, 0]] = 3.5;
        $t[[1, 1]] = 4.5;

        $this->assertEquals(1.5, $t[[0, 0]]);
        $this->assertEquals(2.5, $t[[0, 1]]);
        $this->assertEquals(3.5, $t[[1, 0]]);
        $this->assertEquals(4.5, $t[[1, 1]]);
    }

    /**
     * @covers \SDS\FloatMatrix::offsetGet
     * @covers \SDS\FloatMatrix::get
     *
     * @expectedException \OutOfRangeException
     */
    public function test_set_with_offset_with_too_many_dimensions()
    {
        $t = FloatMatrix::zeros(4, 4);
        $t[[0, 0, 0]];
    }

    /**
     * @covers \SDS\FloatMatrix::offsetGet
     * @covers \SDS\FloatMatrix::get
     *
     * @expectedException \OutOfBoundsException
     */
    public function test_get_with_offset_with_too_big_coordinate_component()
    {
        $t = FloatMatrix::zeros(4, 4);
        $t[[42, 42]];
    }

    /**
     * @covers \SDS\FloatMatrix::offsetSet
     * @covers \SDS\FloatMatrix::set
     *
     * @expectedException \TypeError
     */
    public function test_set_invalid_type_on_FloatMatrix()
    {
        $t = FloatMatrix::zeros(4, 4);
        $t[[0, 0]] = '4.37';
    }
}
