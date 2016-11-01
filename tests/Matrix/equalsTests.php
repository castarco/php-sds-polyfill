<?php
declare(strict_types=1);


namespace SDS\Tests\Matrix;


use SDS\FloatMatrix;
use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class equalsTests extends TestCase
{
    /**
     * @covers \SDS\Matrix::equals
     */
    public function test_shape_equality()
    {
        $this->assertTrue((IntMatrix::zeros(1, 1))->equals(IntMatrix::zeros(1, 1)));
        $this->assertTrue((IntMatrix::zeros(1, 2))->equals(IntMatrix::zeros(1, 2)));
        $this->assertTrue((IntMatrix::zeros(2, 1))->equals(IntMatrix::zeros(2, 1)));
        $this->assertTrue((IntMatrix::zeros(2, 2))->equals(IntMatrix::zeros(2, 2)));
    }

    /**
     * @covers \SDS\Matrix::equals
     */
    public function test_shape_inequality()
    {
        $this->assertFalse((IntMatrix::zeros(1, 1))->equals(IntMatrix::zeros(1, 2)));
        $this->assertFalse((IntMatrix::zeros(1, 1))->equals(IntMatrix::zeros(2, 1)));
        $this->assertFalse((IntMatrix::zeros(1, 1))->equals(IntMatrix::zeros(2, 2)));

        $this->assertFalse((IntMatrix::zeros(1, 2))->equals(IntMatrix::zeros(1, 1)));
        $this->assertFalse((IntMatrix::zeros(1, 2))->equals(IntMatrix::zeros(2, 1)));
        $this->assertFalse((IntMatrix::zeros(1, 2))->equals(IntMatrix::zeros(2, 2)));

        $this->assertFalse((IntMatrix::zeros(2, 1))->equals(IntMatrix::zeros(1, 1)));
        $this->assertFalse((IntMatrix::zeros(2, 1))->equals(IntMatrix::zeros(1, 2)));
        $this->assertFalse((IntMatrix::zeros(2, 1))->equals(IntMatrix::zeros(2, 2)));

        $this->assertFalse((IntMatrix::zeros(2, 2))->equals(IntMatrix::zeros(1, 1)));
        $this->assertFalse((IntMatrix::zeros(2, 2))->equals(IntMatrix::zeros(1, 2)));
        $this->assertFalse((IntMatrix::zeros(2, 2))->equals(IntMatrix::zeros(2, 1)));
    }

    /**
     * @covers \SDS\Matrix::equals
     */
    public function test_class_inequality()
    {
        $this->assertFalse((FloatMatrix::zeros(1, 1))->equals(IntMatrix::zeros(1, 1)));
        $this->assertFalse((IntMatrix::zeros(1, 1))->equals(FloatMatrix::zeros(1, 1)));
    }

    /**
     * @covers \SDS\Matrix::equals
     */
    public function test_inequality_after_setting_value()
    {
        $t1 = IntMatrix::zeros(2, 2);
        $t2 = IntMatrix::zeros(2, 2);

        $t1[[0, 0]] = 42;

        $this->assertFalse($t1->equals($t2));
        $this->assertFalse($t2->equals($t1));
    }

    /**
     * @covers \SDS\Matrix::equals
     */
    public function test_equality_after_setting_value()
    {
        $t1 = IntMatrix::zeros(2, 2);
        $t2 = IntMatrix::zeros(2, 2);

        $t1[[0, 0]] = 42;
        $t2[[0, 0]] = 42;

        $this->assertTrue($t1->equals($t2));
        $this->assertTrue($t2->equals($t1));
    }
}