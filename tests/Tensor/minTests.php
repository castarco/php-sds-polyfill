<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class minTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::min
     * @covers \SDS\Tensor::checkDimsSelector
     * @covers \SDS\Tensor::collapseDims
     * @covers \SDS\Tensor::fastGetInternalIndex
     */
    function test()
    {
        $t1 = IntTensor::fromArray([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);

        $total = $t1->min();
        $summedCols = $t1->min([true, false]);
        $summedRows = $t1->min([false, true]);

        $this->assertEquals(1, $total);

        $this->assertEquals(1, $summedCols[[0]]);
        $this->assertEquals(2, $summedCols[[1]]);
        $this->assertEquals(3, $summedCols[[2]]);

        $this->assertEquals(1, $summedRows[[0]]);
        $this->assertEquals(4, $summedRows[[1]]);
        $this->assertEquals(7, $summedRows[[2]]);
    }
}
