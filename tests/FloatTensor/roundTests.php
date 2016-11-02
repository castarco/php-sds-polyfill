<?php
declare(strict_types=1);


namespace SDS\Tests\FloatTensor;


use SDS\FloatTensor;

use PHPUnit\Framework\TestCase;


class roundTests extends TestCase
{
    /**
     * @covers \SDS\FloatTensor::round
     */
    public function test_round_default()
    {
        $t1 = FloatTensor::fromArray([
            [1.4, 2.4, 3.5],
            [4.4, 5.5, 6.6],
            [7.5, 8.6, 9.6]
        ]);
        $t2 = $t1->round();

        $this->assertEquals(1.4, $t1[[0, 0]]);
        $this->assertEquals(2.4, $t1[[0, 1]]);
        $this->assertEquals(3.5, $t1[[0, 2]]);
        $this->assertEquals(4.4, $t1[[1, 0]]);
        $this->assertEquals(5.5, $t1[[1, 1]]);
        $this->assertEquals(6.6, $t1[[1, 2]]);
        $this->assertEquals(7.5, $t1[[2, 0]]);
        $this->assertEquals(8.6, $t1[[2, 1]]);
        $this->assertEquals(9.6, $t1[[2, 2]]);

        $this->assertEquals(1,  $t2[[0, 0]]);
        $this->assertEquals(2,  $t2[[0, 1]]);
        $this->assertEquals(4,  $t2[[0, 2]]);
        $this->assertEquals(4,  $t2[[1, 0]]);
        $this->assertEquals(6,  $t2[[1, 1]]);
        $this->assertEquals(7,  $t2[[1, 2]]);
        $this->assertEquals(8,  $t2[[2, 0]]);
        $this->assertEquals(9,  $t2[[2, 1]]);
        $this->assertEquals(10, $t2[[2, 2]]);

        $this->assertInstanceOf('SDS\\FloatTensor', $t2);
    }

    /**
     * @covers \SDS\FloatTensor::round
     */
    public function test_round_inPlace()
    {
        $t1 = FloatTensor::fromArray([
            [1.4, 2.4, 3.5],
            [4.4, 5.5, 6.6],
            [7.5, 8.6, 9.6]
        ]);
        $t1->round(true);

        $this->assertEquals(1,  $t1[[0, 0]]);
        $this->assertEquals(2,  $t1[[0, 1]]);
        $this->assertEquals(4,  $t1[[0, 2]]);
        $this->assertEquals(4,  $t1[[1, 0]]);
        $this->assertEquals(6,  $t1[[1, 1]]);
        $this->assertEquals(7,  $t1[[1, 2]]);
        $this->assertEquals(8,  $t1[[2, 0]]);
        $this->assertEquals(9,  $t1[[2, 1]]);
        $this->assertEquals(10, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatTensor::round
     */
    public function test_round_asIntTensor()
    {
        $t1 = FloatTensor::fromArray([
            [1.4, 2.4, 3.5],
            [4.4, 5.5, 6.6],
            [7.5, 8.6, 9.6]
        ]);
        $t2 = $t1->round(false, true);

        $this->assertEquals(1.4, $t1[[0, 0]]);
        $this->assertEquals(2.4, $t1[[0, 1]]);
        $this->assertEquals(3.5, $t1[[0, 2]]);
        $this->assertEquals(4.4, $t1[[1, 0]]);
        $this->assertEquals(5.5, $t1[[1, 1]]);
        $this->assertEquals(6.6, $t1[[1, 2]]);
        $this->assertEquals(7.5, $t1[[2, 0]]);
        $this->assertEquals(8.6, $t1[[2, 1]]);
        $this->assertEquals(9.6, $t1[[2, 2]]);

        $this->assertEquals(1,  $t2[[0, 0]]);
        $this->assertEquals(2,  $t2[[0, 1]]);
        $this->assertEquals(4,  $t2[[0, 2]]);
        $this->assertEquals(4,  $t2[[1, 0]]);
        $this->assertEquals(6,  $t2[[1, 1]]);
        $this->assertEquals(7,  $t2[[1, 2]]);
        $this->assertEquals(8,  $t2[[2, 0]]);
        $this->assertEquals(9,  $t2[[2, 1]]);
        $this->assertEquals(10, $t2[[2, 2]]);

        $this->assertInstanceOf('SDS\\IntTensor', $t2);
    }

    /**
     * @covers \SDS\FloatTensor::ceil
     */
    public function test_ceil_default()
    {
        $t1 = FloatTensor::fromArray([
            [1.4, 2.4, 3.5],
            [4.4, 5.5, 6.6],
            [7.5, 8.6, 9.6]
        ]);
        $t2 = $t1->ceil();

        $this->assertEquals(1.4, $t1[[0, 0]]);
        $this->assertEquals(2.4, $t1[[0, 1]]);
        $this->assertEquals(3.5, $t1[[0, 2]]);
        $this->assertEquals(4.4, $t1[[1, 0]]);
        $this->assertEquals(5.5, $t1[[1, 1]]);
        $this->assertEquals(6.6, $t1[[1, 2]]);
        $this->assertEquals(7.5, $t1[[2, 0]]);
        $this->assertEquals(8.6, $t1[[2, 1]]);
        $this->assertEquals(9.6, $t1[[2, 2]]);

        $this->assertEquals(2,  $t2[[0, 0]]);
        $this->assertEquals(3,  $t2[[0, 1]]);
        $this->assertEquals(4,  $t2[[0, 2]]);
        $this->assertEquals(5,  $t2[[1, 0]]);
        $this->assertEquals(6,  $t2[[1, 1]]);
        $this->assertEquals(7,  $t2[[1, 2]]);
        $this->assertEquals(8,  $t2[[2, 0]]);
        $this->assertEquals(9,  $t2[[2, 1]]);
        $this->assertEquals(10, $t2[[2, 2]]);

        $this->assertInstanceOf('SDS\\FloatTensor', $t2);
    }

    /**
     * @covers \SDS\FloatTensor::ceil
     */
    public function test_ceil_inPlace()
    {
        $t1 = FloatTensor::fromArray([
            [1.4, 2.4, 3.5],
            [4.4, 5.5, 6.6],
            [7.5, 8.6, 9.6]
        ]);
        $t1->ceil(true);

        $this->assertEquals(2,  $t1[[0, 0]]);
        $this->assertEquals(3,  $t1[[0, 1]]);
        $this->assertEquals(4,  $t1[[0, 2]]);
        $this->assertEquals(5,  $t1[[1, 0]]);
        $this->assertEquals(6,  $t1[[1, 1]]);
        $this->assertEquals(7,  $t1[[1, 2]]);
        $this->assertEquals(8,  $t1[[2, 0]]);
        $this->assertEquals(9,  $t1[[2, 1]]);
        $this->assertEquals(10, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatTensor::ceil
     */
    public function test_ceil_asIntTensor()
    {
        $t1 = FloatTensor::fromArray([
            [1.4, 2.4, 3.5],
            [4.4, 5.5, 6.6],
            [7.5, 8.6, 9.6]
        ]);
        $t2 = $t1->ceil(false, true);

        $this->assertEquals(1.4, $t1[[0, 0]]);
        $this->assertEquals(2.4, $t1[[0, 1]]);
        $this->assertEquals(3.5, $t1[[0, 2]]);
        $this->assertEquals(4.4, $t1[[1, 0]]);
        $this->assertEquals(5.5, $t1[[1, 1]]);
        $this->assertEquals(6.6, $t1[[1, 2]]);
        $this->assertEquals(7.5, $t1[[2, 0]]);
        $this->assertEquals(8.6, $t1[[2, 1]]);
        $this->assertEquals(9.6, $t1[[2, 2]]);

        $this->assertEquals(2,  $t2[[0, 0]]);
        $this->assertEquals(3,  $t2[[0, 1]]);
        $this->assertEquals(4,  $t2[[0, 2]]);
        $this->assertEquals(5,  $t2[[1, 0]]);
        $this->assertEquals(6,  $t2[[1, 1]]);
        $this->assertEquals(7,  $t2[[1, 2]]);
        $this->assertEquals(8,  $t2[[2, 0]]);
        $this->assertEquals(9,  $t2[[2, 1]]);
        $this->assertEquals(10, $t2[[2, 2]]);

        $this->assertInstanceOf('SDS\\IntTensor', $t2);
    }

    /**
     * @covers \SDS\FloatTensor::floor
     */
    public function test_floor_default()
    {
        $t1 = FloatTensor::fromArray([
            [1.4, 2.4, 3.5],
            [4.4, 5.5, 6.6],
            [7.5, 8.6, 9.6]
        ]);
        $t2 = $t1->floor();

        $this->assertEquals(1.4, $t1[[0, 0]]);
        $this->assertEquals(2.4, $t1[[0, 1]]);
        $this->assertEquals(3.5, $t1[[0, 2]]);
        $this->assertEquals(4.4, $t1[[1, 0]]);
        $this->assertEquals(5.5, $t1[[1, 1]]);
        $this->assertEquals(6.6, $t1[[1, 2]]);
        $this->assertEquals(7.5, $t1[[2, 0]]);
        $this->assertEquals(8.6, $t1[[2, 1]]);
        $this->assertEquals(9.6, $t1[[2, 2]]);

        $this->assertEquals(1, $t2[[0, 0]]);
        $this->assertEquals(2, $t2[[0, 1]]);
        $this->assertEquals(3, $t2[[0, 2]]);
        $this->assertEquals(4, $t2[[1, 0]]);
        $this->assertEquals(5, $t2[[1, 1]]);
        $this->assertEquals(6, $t2[[1, 2]]);
        $this->assertEquals(7, $t2[[2, 0]]);
        $this->assertEquals(8, $t2[[2, 1]]);
        $this->assertEquals(9, $t2[[2, 2]]);

        $this->assertInstanceOf('SDS\\FloatTensor', $t2);
    }

    /**
     * @covers \SDS\FloatTensor::floor
     */
    public function test_floor_inPlace()
    {
        $t1 = FloatTensor::fromArray([
            [1.4, 2.4, 3.5],
            [4.4, 5.5, 6.6],
            [7.5, 8.6, 9.6]
        ]);
        $t1->floor(true);

        $this->assertEquals(1, $t1[[0, 0]]);
        $this->assertEquals(2, $t1[[0, 1]]);
        $this->assertEquals(3, $t1[[0, 2]]);
        $this->assertEquals(4, $t1[[1, 0]]);
        $this->assertEquals(5, $t1[[1, 1]]);
        $this->assertEquals(6, $t1[[1, 2]]);
        $this->assertEquals(7, $t1[[2, 0]]);
        $this->assertEquals(8, $t1[[2, 1]]);
        $this->assertEquals(9, $t1[[2, 2]]);
    }

    /**
     * @covers \SDS\FloatTensor::floor
     */
    public function test_floor_asIntTensor()
    {
        $t1 = FloatTensor::fromArray([
            [1.4, 2.4, 3.5],
            [4.4, 5.5, 6.6],
            [7.5, 8.6, 9.6]
        ]);
        $t2 = $t1->floor(false, true);

        $this->assertEquals(1.4, $t1[[0, 0]]);
        $this->assertEquals(2.4, $t1[[0, 1]]);
        $this->assertEquals(3.5, $t1[[0, 2]]);
        $this->assertEquals(4.4, $t1[[1, 0]]);
        $this->assertEquals(5.5, $t1[[1, 1]]);
        $this->assertEquals(6.6, $t1[[1, 2]]);
        $this->assertEquals(7.5, $t1[[2, 0]]);
        $this->assertEquals(8.6, $t1[[2, 1]]);
        $this->assertEquals(9.6, $t1[[2, 2]]);

        $this->assertEquals(1, $t2[[0, 0]]);
        $this->assertEquals(2, $t2[[0, 1]]);
        $this->assertEquals(3, $t2[[0, 2]]);
        $this->assertEquals(4, $t2[[1, 0]]);
        $this->assertEquals(5, $t2[[1, 1]]);
        $this->assertEquals(6, $t2[[1, 2]]);
        $this->assertEquals(7, $t2[[2, 0]]);
        $this->assertEquals(8, $t2[[2, 1]]);
        $this->assertEquals(9, $t2[[2, 2]]);

        $this->assertInstanceOf('SDS\\IntTensor', $t2);
    }
}
