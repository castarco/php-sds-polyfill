<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class stddevTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::stddev
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

        $stddev = $t1->stddev();
        $stddevCols  = $t1->stddev([true, false]);
        $stddevRows  = $t1->stddev([false, true]);

        $this->assertEquals(2.7386, $stddev, '', 0.0001);

        $this->assertEquals(3.0, $stddevCols[[0]]);
        $this->assertEquals(3.0, $stddevCols[[1]]);
        $this->assertEquals(3.0, $stddevCols[[2]]);

        $this->assertEquals(1.0, $stddevRows[[0]]);
        $this->assertEquals(1.0, $stddevRows[[1]]);
        $this->assertEquals(1.0, $stddevRows[[2]]);
    }
}
