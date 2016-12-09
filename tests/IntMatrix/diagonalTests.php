<?php
declare(strict_types=1);


namespace SDS\Tests\IntMatrix;


use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class diagonalTests extends TestCase
{
    /**
     * @covers \SDS\IntMatrix::diagonal
     */
    public function test_diagonal()
    {
        $a = IntMatrix::fromArray([
            [5, 0, 0],
            [0, 5, 0],
            [0, 0, 5]
        ]);

        $b = IntMatrix::diagonal(3, 5);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }

    /**
     * @covers \SDS\IntMatrix::vectorToDiagonal
     */
    public function test_vectorToDiagonal()
    {
        $a = IntMatrix::fromArray([
            [1, 0, 0],
            [0, 2, 0],
            [0, 0, 4]
        ]);

        $b = IntMatrix::vectorToDiagonal(1, 2, 4);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }
}
