<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class maxTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::max
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

        $total = $t1->max();
        $summedCols = $t1->max([true, false]);
        $summedRows = $t1->max([false, true]);

        $this->assertEquals(9, $total);

        $this->assertEquals(7, $summedCols[[0]]);
        $this->assertEquals(8, $summedCols[[1]]);
        $this->assertEquals(9, $summedCols[[2]]);

        $this->assertEquals(3, $summedRows[[0]]);
        $this->assertEquals(6, $summedRows[[1]]);
        $this->assertEquals(9, $summedRows[[2]]);
    }
}
