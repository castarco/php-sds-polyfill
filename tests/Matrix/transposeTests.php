<?php
declare(strict_types=1);


namespace SDS\Tests\Matrix;


use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class transposeTests extends TestCase
{
    /**
     * @covers \SDS\Matrix::transpose
     */
    public function test_A()
    {
        $a = IntMatrix::fromArray([
            [1,  2,  3,  4],
            [5,  6,  7,  8],
            [9, 10, 11, 12]
        ]);
        $aT = IntMatrix::fromArray([
            [1, 5,  9],
            [2, 6, 10],
            [3, 7, 11],
            [4, 8, 12]
        ]);

        $this->assertTrue($aT->equals($a->transpose()));
        $this->assertTrue($a->equals($aT->transpose()));
        $this->assertTrue($a->equals($a->transpose()->transpose()));
        $this->assertTrue($aT->equals($aT->transpose()->transpose()));
    }
}
