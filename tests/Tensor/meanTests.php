<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class meanTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::mean
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

        $mean = $t1->mean();
        $meanCols = $t1->mean([true, false]);
        $meanRows = $t1->mean([false, true]);

        $this->assertEquals(5.0, $mean);

        $this->assertEquals(4.0, $meanCols[[0]]);
        $this->assertEquals(5.0, $meanCols[[1]]);
        $this->assertEquals(6.0, $meanCols[[2]]);

        $this->assertEquals(2.0, $meanRows[[0]]);
        $this->assertEquals(5.0, $meanRows[[1]]);
        $this->assertEquals(8.0, $meanRows[[2]]);
    }
}
