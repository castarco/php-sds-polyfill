<?php
declare(strict_types=1);


namespace SDS\Tests\Matrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class traceTests extends TestCase
{
    /**
     * @covers \SDS\Matrix::trace
     */
    public function test_A()
    {
        $a = FloatMatrix::fromArray([
            [1, 3, 5],
            [7, 11, 13],
            [17, 19, 23]
        ]);

        $this->assertEquals(35, $a->trace());
    }
}
