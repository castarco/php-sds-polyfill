<?php
declare(strict_types=1);


namespace SDS\Tests\Matrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class qrDecompositionTests extends TestCase
{
    /**
     * @covers \SDS\Matrix::qrDecomposition
     */
    public function test_A()
    {
        $a = FloatMatrix::fromArray([
            [ 1,   3,   5],
            [ 7,  11,  13],
            [17,  19,  23]
        ]);

        list($q, $r) = $a->qrDecomposition();

        $this->assertEquals([3, 3], $q->getShape());
        $this->assertEquals([3, 3], $r->getShape());

        $this->assertEquals(0.0, $r[[1, 0]], '', 1.0E16);
        $this->assertEquals(0.0, $r[[2, 0]], '', 1.0E16);
        $this->assertEquals(0.0, $r[[2, 1]], '', 1.0E16);

        $this->assertTrue($a->equals($q->matMul($r), 1.0E-14));
    }

    /**
     * @covers \SDS\Matrix::qrDecomposition
     */
    public function test_B()
    {
        $a = FloatMatrix::fromArray([
            [ 1,  3,  5, 29],
            [ 7, 11, 13, 31],
            [17, 19, 23, 37]
        ]);

        list($q, $r) = $a->qrDecomposition();

        $this->assertEquals([3, 3], $q->getShape());
        $this->assertEquals([3, 4], $r->getShape());

        $this->assertEquals(0.0, $r[[1, 0]], '', 1.0E16);
        $this->assertEquals(0.0, $r[[2, 0]], '', 1.0E16);
        $this->assertEquals(0.0, $r[[2, 1]], '', 1.0E16);

        $this->assertTrue($a->equals($q->matMul($r), 1.0E-14));
    }

    /**
     * @covers \SDS\Matrix::qrDecomposition
     */
    public function test_C()
    {
        $a = FloatMatrix::fromArray([
            [ 1,  3,  5],
            [ 7, 11, 13],
            [17, 19, 23],
            [29, 31, 37]
        ]);

        list($q, $r) = $a->qrDecomposition();

        $this->assertEquals([4, 3], $q->getShape());
        $this->assertEquals([3, 3], $r->getShape());

        $this->assertEquals(0.0, $r[[1, 0]], '', 1.0E16);
        $this->assertEquals(0.0, $r[[2, 0]], '', 1.0E16);
        $this->assertEquals(0.0, $r[[2, 1]], '', 1.0E16);

        // Temporary disabled test
        // $this->assertTrue($a->equals($q->matMul($r), 0.1));
    }
}
