<?php
declare(strict_types=1);


namespace SDS\Tests\FloatMatrix;


use SDS\FloatMatrix;

use PHPUnit\Framework\TestCase;


class randomBinomialTests extends TestCase
{
    public function MatrixProvider()
    {
        return [
            // mu, Matrix,                                     sigma
            [0.0,  FloatMatrix::randomNormal(32, 32),          1.0],
            [25.0, FloatMatrix::randomNormal(32, 32, 25),      1.0],
            [0.0,  FloatMatrix::randomNormal(32, 32, 0, 0.5),  0.5],
            [25.0, FloatMatrix::randomNormal(32, 32, 25, 0.5), 0.5],
        ];
    }

    /**
     * @dataProvider MatrixProvider
     *
     * @param float       $center
     * @param FloatMatrix $M
     */
    public function test_symmetry(float $center, FloatMatrix $M)
    {
        $positives = 0;
        $negatives = 0;
        for ($i=0; $i<32; $i++) {
            for ($j=0; $j<32; $j++) {
                if ($M[[$i, $j]] > $center) {
                    $positives++;
                } elseif ($M[[$i, $j]] < $center) {
                    $negatives++;
                }
            }
        }

        $this->assertGreaterThan(450, $positives);
        $this->assertGreaterThan(450, $negatives);
    }

    /**
     * @dataProvider MatrixProvider
     *
     * @param float       $center
     * @param FloatMatrix $M
     */
    public function test_mean(float $center, FloatMatrix $M)
    {
        $acc = 0;
        for ($i = 0; $i < 32; $i++) {
            for ($j = 0; $j < 32; $j++) {
                $acc += $M[[$i, $j]];
            }
        }
        $acc /= 1024.;

        $this->assertEquals($center, $acc, '', 0.09);
    }

    /**
     * @dataProvider MatrixProvider
     *
     * @param float       $center
     * @param FloatMatrix $M
     * @param float       $sigma
     */
    public function test_variance(float $center, FloatMatrix $M, float $sigma)
    {
        $acc = 0;
        for ($i=0; $i<32; $i++) {
            for ($j=0; $j<32; $j++) {
                $acc += pow($M[[$i, $j]]-$center, 2);
            }
        }
        $acc = sqrt($acc / 1024);

        $this->assertEquals($sigma, $acc, '', $sigma * 0.08);
    }

    /**
     * @covers \SDS\FloatMatrix::randomNormal
     */
    public function test_randomness()
    {
        $rM1 = FloatMatrix::randomNormal(3, 3);
        $rM2 = FloatMatrix::randomNormal(3, 3);

        $this->assertFalse($rM1->equals($rM2));
        $this->assertFalse($rM2->equals($rM1));
    }
}