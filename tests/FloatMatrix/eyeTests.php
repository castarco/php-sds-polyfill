<?php
declare(strict_types=1);


namespace SDS\Tests\FloatMatrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class eyeTests extends TestCase
{
    /**
     * @covers \SDS\FloatMatrix::eye
     */
    public function test_eye()
    {
        $a = FloatMatrix::fromArray([
            [5, 0, 0],
            [0, 5, 0],
            [0, 0, 5]
        ]);

        $b = FloatMatrix::eye(3, 5);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }

    /**
     * @covers \SDS\FloatMatrix::eye
     */
    public function test_eye_ugly()
    {
        $a1 = FloatMatrix::fromArray([
            [5, 0, 0, 0],
            [0, 5, 0, 0],
            [0, 0, 5, 0]
        ]);
        $a2 = FloatMatrix::eye(3, 5, 4);

        $b1 = FloatMatrix::fromArray([
            [5, 0, 0],
            [0, 5, 0],
            [0, 0, 5],
            [0, 0, 0]
        ]);
        $b2 = FloatMatrix::eye(4, 5, 3);

        $this->assertTrue($a1->equals($a2));
        $this->assertTrue($a2->equals($a1));
        $this->assertTrue($b1->equals($b2));
        $this->assertTrue($b2->equals($b1));
    }

    /**
     * @covers \SDS\FloatMatrix::diagonal
     */
    public function test_diagonal()
    {
        $a = FloatMatrix::fromArray([
            [1, 0, 0],
            [0, 2, 0],
            [0, 0, 4]
        ]);

        $b = FloatMatrix::diagonal(1, 2, 4);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }
}
