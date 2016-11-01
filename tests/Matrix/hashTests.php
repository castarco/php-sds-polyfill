<?php
declare(strict_types=1);


namespace SDS\Tests\Matrix;


use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class hashTests extends TestCase
{
    /**
     * @covers \SDS\Matrix::hash
     */
    public function test_shape_equality()
    {
        $this->assertEquals((IntMatrix::zeros(1, 1))->hash(), (IntMatrix::zeros(1, 1))->hash());
        $this->assertEquals((IntMatrix::zeros(1, 2))->hash(), (IntMatrix::zeros(1, 2))->hash());
        $this->assertEquals((IntMatrix::zeros(2, 1))->hash(), (IntMatrix::zeros(2, 1))->hash());
        $this->assertEquals((IntMatrix::zeros(2, 2))->hash(), (IntMatrix::zeros(2, 2))->hash());
    }

    /**
     * @covers \SDS\Matrix::hash
     */
    public function test_inequality_after_setting_value()
    {
        $t1 = IntMatrix::zeros(2, 2);
        $t2 = IntMatrix::zeros(2, 2);

        $t1[[0, 0]] = 42;

        $this->assertNotEquals($t1->hash(), $t2->hash());
    }

    /**
     * @covers \SDS\Matrix::hash
     */
    public function test_equality_after_setting_value()
    {
        $t1 = IntMatrix::zeros(2, 2);
        $t2 = IntMatrix::zeros(2, 2);

        $t1[[0, 0]] = 42;
        $t2[[0, 0]] = 42;

        $this->assertEquals($t1->hash(), $t2->hash());
    }
}