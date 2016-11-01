<?php
declare(strict_types=1);


namespace SDS\Tests\Matrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class ArrayAccessTests extends TestCase
{
    /**
     * @covers \SDS\Matrix::offsetExists
     */
    public function test_isset_on_invalid_offset()
    {
        $t = FloatMatrix::zeros(4, 4);

        $this->assertFalse(isset($t[[5]]));
        $this->assertFalse(isset($t[[5, 1]]));
        $this->assertFalse(isset($t[[1, 5]]));
        $this->assertFalse(isset($t[0]));
        $this->assertFalse(isset($t['0']));
    }

    /**
     * @covers \SDS\Matrix::offsetExists
     */
    public function test_isset_on_valid_offset()
    {
        $t1 = FloatMatrix::zeros(2, 2);

        $this->assertTrue(isset($t1[[0, 0]]));
        $this->assertTrue(isset($t1[[0, 1]]));
        $this->assertTrue(isset($t1[[1, 0]]));
        $this->assertTrue(isset($t1[[1, 1]]));

        $t2 = FloatMatrix::zeros(1, 2);

        $this->assertTrue(isset($t2[[0, 0]]));
        $this->assertTrue(isset($t2[[0, 1]]));

        $t3 = FloatMatrix::zeros(2, 1);

        $this->assertTrue(isset($t3[[1, 0]]));
        $this->assertTrue(isset($t3[[1, 0]]));
    }

    /**
     * @covers \SDS\Matrix::offsetUnset
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Not supported operation
     */
    public function test_unset()
    {
        $t1 = FloatMatrix::zeros(4, 4);
        unset($t1[[0, 0]]);
    }
}
