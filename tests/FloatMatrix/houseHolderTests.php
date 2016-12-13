<?php
declare(strict_types=1);


namespace SDS\Tests\FloatMatrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class householderTests extends TestCase
{
    /**
     * @covers \SDS\FloatMatrix::householder
     */
    public function test_A()
    {
        $v = FloatMatrix::fromArray([1, 2, 3], 3, 1);
        $h = FloatMatrix::householder($v);

        $this->assertTrue(
            $h->equals(FloatMatrix::fromArray([
                [-0.26726124, -0.53452248, -0.80178373],
                [-0.53452248,  0.77454192, -0.33818712],
                [-0.80178373, -0.33818712,  0.49271932]
            ]), 1.0E-8)
        );
    }

    /**
     * @covers \SDS\FloatMatrix::householder
     */
    public function test_B()
    {
        $v = FloatMatrix::fromArray([3, 2, 1], 3, 1);
        $h = FloatMatrix::householder($v);

        $this->assertTrue(
            $h->equals(FloatMatrix::fromArray([
                [-0.80178373, -0.53452248, -0.26726124],
                [-0.53452248,  0.84142698, -0.07928651],
                [-0.26726124, -0.07928651,  0.96035675]
            ]), 1.0E-8)
        );
    }
}
