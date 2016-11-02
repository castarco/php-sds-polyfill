<?php
declare(strict_types=1);


namespace SDS\Tests\FloatMatrix;


use SDS\FloatMatrix;
use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class setSliceTests extends TestCase
{
    /**
     * @covers \SDS\FloatMatrix::setSlice
     */
    public function test_with_FloatMatrix()
    {
        $t1 = FloatMatrix::zeros(3, 3);
        $t2 = FloatMatrix::ones(2, 2);

        $t1->setSlice($t2, [[1, 2], [1, 2]]);

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatMatrix::setSlice
     */
    public function test_with_IntMatrix()
    {
        $t1 = FloatMatrix::zeros(3, 3);
        $t2 = IntMatrix::ones(2, 2);

        $t1->setSlice($t2, [[1, 2], [1, 2]]);

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatMatrix::offsetSet
     * @covers \SDS\FloatMatrix::setSlice
     */
    public function test_with_ArrayAccess_idiom()
    {
        $t1 = FloatMatrix::zeros(3, 3);
        $t2 = FloatMatrix::ones(2, 2);

        $t1[[[1, 2], [1, 2]]] = $t2;

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatMatrix::offsetSet
     * @covers \SDS\FloatMatrix::setArrayAsSlice
     */
    public function test_with_ArrayAccess_idiom_and_array_param()
    {
        $t1 = FloatMatrix::zeros(3, 3);

        $t1[[[1, 2], [1, 2]]] = [[1, 1], [1, 1]];

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatMatrix::offsetSet
     * @covers \SDS\FloatMatrix::setArrayAsSlice
     */
    public function test_with_ArrayAccess_idiom_and_flat_array_param()
    {
        $t1 = FloatMatrix::zeros(3, 3);

        $t1[[[1, 2], [1, 2]]] = [1, 1, 1, 1];

        $this->assertEquals(0, $t1[[0, 0]]);
        $this->assertEquals(0, $t1[[0, 1]]);
        $this->assertEquals(0, $t1[[0, 2]]);
        $this->assertEquals(0, $t1[[1, 0]]);
        $this->assertEquals(0, $t1[[2, 0]]);
        $this->assertEquals(1, $t1[[1, 1]]);
        $this->assertEquals(1, $t1[[1, 2]]);
        $this->assertEquals(1, $t1[[2, 1]]);
        $this->assertEquals(1, $t1[[2, 2]]);
    }
}
