<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class sumTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::sum
     * @covers \SDS\Tensor::checkDimsSelector
     * @covers \SDS\Tensor::collapseDims
     */
    function test()
    {
        $t1 = IntTensor::fromArray([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);

        $total = $t1->sum();
        $summedCols = $t1->sum([true, false]);
        $summedRows = $t1->sum([false, true]);

        $this->assertEquals(45, $total);

        $this->assertEquals(12, $summedCols[[0]]);
        $this->assertEquals(15, $summedCols[[1]]);
        $this->assertEquals(18, $summedCols[[2]]);

        $this->assertEquals(6, $summedRows[[0]]);
        $this->assertEquals(15, $summedRows[[1]]);
        $this->assertEquals(24, $summedRows[[2]]);
    }
}
