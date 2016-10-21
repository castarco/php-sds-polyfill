<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class TraversableTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::getIterator
     */
    public function test()
    {
        $t = IntTensor::zeros([2, 2]);

        $t[[0, 0]] = 1;
        $t[[0, 1]] = 2;
        $t[[1, 0]] = 3;
        $t[[1, 1]] = 4;

        $acc = 0;
        foreach ($t as $cell) {
            $acc += $cell;
        }

        $this->assertEquals(10, $acc);
    }
}
