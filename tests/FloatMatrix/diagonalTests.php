<?php
declare(strict_types=1);


namespace SDS\Tests\FloatMatrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class diagonalTests extends TestCase
{
    /**
     * @covers \SDS\FloatMatrix::diagonal
     */
    public function test_diagonal()
    {
        $a = FloatMatrix::fromArray([
            [5, 0, 0],
            [0, 5, 0],
            [0, 0, 5]
        ]);

        $b = FloatMatrix::diagonal(3, 5);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }

    /**
     * @covers \SDS\FloatMatrix::vectorToDiagonal
     */
    public function test_vectorToDiagonal()
    {
        $a = FloatMatrix::fromArray([
            [1, 0, 0],
            [0, 2, 0],
            [0, 0, 4]
        ]);

        $b = FloatMatrix::vectorToDiagonal(1, 2, 4);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }
}
