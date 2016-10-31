<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class varianceTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::variance
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

        $variance = $t1->variance();
        $varCols  = $t1->variance([true, false]);
        $varRows  = $t1->variance([false, true]);

        $this->assertEquals(7.5, $variance);

        $this->assertEquals(9.0, $varCols[[0]]);
        $this->assertEquals(9.0, $varCols[[1]]);
        $this->assertEquals(9.0, $varCols[[2]]);

        $this->assertEquals(1.0, $varRows[[0]]);
        $this->assertEquals(1.0, $varRows[[1]]);
        $this->assertEquals(1.0, $varRows[[2]]);
    }
}
