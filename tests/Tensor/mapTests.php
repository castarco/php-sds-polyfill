<?php
declare(strict_types=1);


namespace SDS\Tests\Tensor;


use SDS\IntTensor;

use PHPUnit\Framework\TestCase;


class mapTests extends TestCase
{
    /**
     * @covers \SDS\Tensor::map
     */
    public function test_map()
    {
        $t = IntTensor::fromArray([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);

        $t2 = $t->map(function (int $x) : int {
            return $x*$x;
        });

        $this->assertFalse($t->equals($t2));
        $this->assertFalse($t2->equals($t));

        $this->assertEquals(1, $t[[0, 0]]);
        $this->assertEquals(2, $t[[0, 1]]);
        $this->assertEquals(3, $t[[0, 2]]);
        $this->assertEquals(4, $t[[1, 0]]);
        $this->assertEquals(5, $t[[1, 1]]);
        $this->assertEquals(6, $t[[1, 2]]);
        $this->assertEquals(7, $t[[2, 0]]);
        $this->assertEquals(8, $t[[2, 1]]);
        $this->assertEquals(9, $t[[2, 2]]);

        $this->assertEquals(1, $t2[[0, 0]]);
        $this->assertEquals(4, $t2[[0, 1]]);
        $this->assertEquals(9, $t2[[0, 2]]);
        $this->assertEquals(16, $t2[[1, 0]]);
        $this->assertEquals(25, $t2[[1, 1]]);
        $this->assertEquals(36, $t2[[1, 2]]);
        $this->assertEquals(49, $t2[[2, 0]]);
        $this->assertEquals(64, $t2[[2, 1]]);
        $this->assertEquals(81, $t2[[2, 2]]);
    }

    /**
     * @covers \SDS\Tensor::apply
     */
    public function test_apply()
    {
        $t = IntTensor::fromArray([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);

        $t->apply(function (int $x) : int {
            return $x*$x;
        });

        $this->assertEquals(1, $t[[0, 0]]);
        $this->assertEquals(4, $t[[0, 1]]);
        $this->assertEquals(9, $t[[0, 2]]);
        $this->assertEquals(16, $t[[1, 0]]);
        $this->assertEquals(25, $t[[1, 1]]);
        $this->assertEquals(36, $t[[1, 2]]);
        $this->assertEquals(49, $t[[2, 0]]);
        $this->assertEquals(64, $t[[2, 1]]);
        $this->assertEquals(81, $t[[2, 2]]);
    }
}
