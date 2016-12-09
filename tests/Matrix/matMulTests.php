<?php
declare(strict_types=1);


namespace SDS\Tests\Matrix;


use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class matMulTests extends TestCase
{
    /**
     * @covers \SDS\Matrix::matMul
     */
    public function test_A()
    {
        $a = IntMatrix::fromArray([
            [1,  2,  3,  4],
            [5,  6,  7,  8],
            [9, 10, 11, 12]
        ]);

        $b = IntMatrix::fromArray([
            [13, 14],
            [15, 16],
            [17, 18],
            [19, 20]
        ]);

        $c = $a->matMul($b);

        $this->assertEquals([3, 2], $c->getShape());

        $this->assertEquals(1*13 +  2*15 +  3*17 +  4*19, $c[[0, 0]]);
        $this->assertEquals(5*13 +  6*15 +  7*17 +  8*19, $c[[1, 0]]);
        $this->assertEquals(9*13 + 10*15 + 11*17 + 12*19, $c[[2, 0]]);
        $this->assertEquals(1*14 +  2*16 +  3*18 +  4*20, $c[[0, 1]]);
        $this->assertEquals(5*14 +  6*16 +  7*18 +  8*20, $c[[1, 1]]);
        $this->assertEquals(9*14 + 10*16 + 11*18 + 12*20, $c[[2, 1]]);
    }

    /**
     * @covers \SDS\Matrix::matMul
     */
    public function test_B()
    {
        $a = IntMatrix::fromArray([
            [1, 2],
            [3, 4],
            [5, 6]
        ]);

        $b = IntMatrix::fromArray([
            [7,   8,  9],
            [10, 11, 12]
        ]);

        $c = $a->matMul($b);

        $this->assertEquals([3, 3], $c->getShape());

        $this->assertEquals(1*7 + 2*10, $c[[0, 0]]);
        $this->assertEquals(3*7 + 4*10, $c[[1, 0]]);
        $this->assertEquals(5*7 + 6*10, $c[[2, 0]]);
        $this->assertEquals(1*8 + 2*11, $c[[0, 1]]);
        $this->assertEquals(3*8 + 4*11, $c[[1, 1]]);
        $this->assertEquals(5*8 + 6*11, $c[[2, 1]]);
        $this->assertEquals(1*9 + 2*12, $c[[0, 2]]);
        $this->assertEquals(3*9 + 4*12, $c[[1, 2]]);
        $this->assertEquals(5*9 + 6*12, $c[[2, 2]]);
    }

    /**
     * @covers \SDS\Matrix::matMul
     *
     * @expectedException \SDS\Exceptions\ShapeMismatchException
     */
    public function test_invalidOrder()
    {
        $a = IntMatrix::fromArray([
            [1,  2,  3,  4],
            [5,  6,  7,  8],
            [9, 10, 11, 12]
        ]);

        $b = IntMatrix::fromArray([
            [13, 14],
            [15, 16],
            [17, 18],
            [19, 20]
        ]);

        $b->matMul($a);
    }
}
