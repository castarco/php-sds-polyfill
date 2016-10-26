<?php
declare(strict_types=1);


namespace SDS\Tests\FloatTensor;


use SDS\FloatTensor;

use PHPUnit\Framework\TestCase;


class randomBinomialTests extends TestCase
{
    public function tensorsProvider()
    {
        return [
            // mu, tensor,                                     sigma
            [0.0,  FloatTensor::randomNormal([32, 32]),          1.0],
            [25.0, FloatTensor::randomNormal([32, 32], 25),      1.0],
            [0.0,  FloatTensor::randomNormal([32, 32], 0, 0.5),  0.5],
            [25.0, FloatTensor::randomNormal([32, 32], 25, 0.5), 0.5],
        ];
    }

    /**
     * @dataProvider tensorsProvider
     *
     * @param float       $center
     * @param FloatTensor $T
     */
    public function test_symmetry(float $center, FloatTensor $T)
    {
        $positives = 0;
        $negatives = 0;
        for ($i=0; $i<32; $i++) {
            for ($j=0; $j<32; $j++) {
                if ($T[[$i,$j]] > $center) {
                    $positives++;
                } elseif ($T[[$i,$j]] < $center) {
                    $negatives++;
                }
            }
        }

        $this->assertGreaterThan(460, $positives);
        $this->assertGreaterThan(460, $negatives);
    }

    /**
     * @dataProvider tensorsProvider
     *
     * @param float       $center
     * @param FloatTensor $T
     */
    public function test_mean(float $center, FloatTensor $T)
    {
        $acc = 0;
        for ($i=0; $i<32; $i++) {
            for ($j=0; $j<32; $j++) {
                $acc += $T[[$i, $j]];
            }
        }
        $acc /= 1024.;

        $this->assertEquals($center, $acc, '', 0.09);
    }

    /**
     * @dataProvider tensorsProvider
     *
     * @param float       $center
     * @param FloatTensor $T
     * @param float       $sigma
     */
    public function test_variance(float $center, FloatTensor $T, float $sigma)
    {
        $acc = 0;
        for ($i=0; $i<32; $i++) {
            for ($j=0; $j<32; $j++) {
                $acc += pow($T[[$i, $j]]-$center, 2);
            }
        }
        $acc = sqrt($acc / 1024);

        $this->assertEquals($sigma, $acc, '', $sigma * 0.08);
    }

    /**
     * @covers \SDS\FloatTensor::randomNormal
     */
    public function test_randomness()
    {
        $rT1 = FloatTensor::randomNormal([3, 3]);
        $rT2 = FloatTensor::randomNormal([3, 3]);

        $this->assertFalse($rT1->equals($rT2));
        $this->assertFalse($rT2->equals($rT1));
    }
}