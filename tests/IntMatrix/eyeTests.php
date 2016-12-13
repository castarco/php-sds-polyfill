<?php
declare(strict_types=1);


namespace SDS\Tests\IntMatrix;


use SDS\IntMatrix;

use PHPUnit\Framework\TestCase;


class eyeTests extends TestCase
{
    /**
     * @covers \SDS\IntMatrix::eye
     */
    public function test_eye()
    {
        $a = IntMatrix::fromArray([
            [5, 0, 0],
            [0, 5, 0],
            [0, 0, 5]
        ]);

        $b = IntMatrix::eye(3, 5);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }

    /**
     * @covers \SDS\FloatMatrix::eye
     */
    public function test_eye_ugly()
    {
        $a1 = IntMatrix::fromArray([
            [5, 0, 0, 0],
            [0, 5, 0, 0],
            [0, 0, 5, 0]
        ]);
        $a2 = IntMatrix::eye(3, 5, 4);

        $b1 = IntMatrix::fromArray([
            [5, 0, 0],
            [0, 5, 0],
            [0, 0, 5],
            [0, 0, 0]
        ]);
        $b2 = IntMatrix::eye(4, 5, 3);

        $this->assertTrue($a1->equals($a2));
        $this->assertTrue($a2->equals($a1));
        $this->assertTrue($b1->equals($b2));
        $this->assertTrue($b2->equals($b1));
    }

    /**
     * @covers \SDS\IntMatrix::diagonal
     */
    public function test_diagonal()
    {
        $a = IntMatrix::fromArray([
            [1, 0, 0],
            [0, 2, 0],
            [0, 0, 4]
        ]);

        $b = IntMatrix::diagonal(1, 2, 4);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }
}
