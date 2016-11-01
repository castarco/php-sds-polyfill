<?php
declare(strict_types=1);


namespace SDS;


use Ds\Vector;
use SDS\Exceptions\ShapeMismatchException;
use function SDS\functions\isAssociativeArray;


final class FloatMatrix extends Matrix
{
    // -----------------------------------------------------------------------------------------------------------------
    // Static factories:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param int $height
     * @param int $width
     * @return FloatMatrix
     */
    public static function zeros (int $height, int $width) : FloatMatrix
    {
        return self::constant(0.0, $height, $width);
    }

    /**
     * @param int $height
     * @param int $width
     * @return FloatMatrix
     */
    public static function ones (int $height, int $width) : FloatMatrix
    {
        return self::constant(1.0, $height, $width);
    }

    /**
     * @param float $c
     * @param int $height
     * @param int $width
     * @return FloatMatrix
     */
    public static function constant (float $c, int $height, int $width) : FloatMatrix
    {
        $m = new FloatMatrix($height, $width);
        $m->data = new Vector(\array_fill(0, $height * $width, $c));

        return $m;
    }

    /**
     * @param int $height
     * @param int $width
     * @param float $maxL
     * @param float $minL
     * @return FloatMatrix
     */
    public static function randomUniform(int $height, int $width, float $maxL = 1.0, float $minL = 0.0) : FloatMatrix
    {
        $m = new FloatMatrix($height, $width);
        $m->data = new Vector();

        $dataSize = $height * $width;
        $m->data->allocate($dataSize);

        $factor = ($maxL - $minL) / \getrandmax();
        for ($i = 0; $i < $dataSize; $i++) {
            $m->data->push(\rand()*$factor + $minL);
        }

        return $m;
    }

    /**
     * @param int $height
     * @param int $width
     * @param float $mu
     * @param float $sigma
     * @return FloatMatrix
     */
    public static function randomNormal(int $height, int $width, float $mu = 0.0, float $sigma = 1.0) : FloatMatrix
    {
        $m = new FloatMatrix($height, $width);
        $m->data = new Vector();

        $dataSize = $height * $width;
        $m->data->allocate($dataSize);

        $divisor = \getrandmax();
        $two_pi  = 2.0 * 3.14159265358979323846;

        // Box-Muller Transform
        for ($i=0; $i<$dataSize; $i++) {
            $u1 = \rand() / $divisor;
            $u2 = \rand() / $divisor;

            $z0 = $sigma * (\sqrt(-2.0 * \log($u1)) * \cos($two_pi * $u2)) + $mu;
            $z1 = \sqrt(-2.0 * \log($u1)) * \sin($two_pi * $u2);

            $m->data->push($z0);
            $i++;

            if ($i < $dataSize) {
                // We do here the product to avoid doing it when it's not useful.
                $m->data->push($sigma * $z1 + $mu);
            } else {
                // To avoid the extra comparison in the for loop "header".
                break;
            }
        }

        return $m;
    }

    /**
     * @param int[]|float[]|array[] $source
     * @param null|int $height
     * @param null|int $width
     * @return FloatMatrix
     */
    public static function fromArray(array $source, int $height = null, int $width = null) : FloatMatrix
    {
        if (($len = \count($source)) === 0) {
            throw new \InvalidArgumentException();
        }

        if (null !== $height && null !== $width && (\is_float($source[0]) || \is_int($source[0]))) {

            if ($height * $width !== $len) {
                throw new \InvalidArgumentException();
            }

            $m = new FloatMatrix($height, $width);

            $m->data = new Vector($source);
            $m->data->apply(function ($x) { return (float)$x; });

            return $m;

        } elseif (null === $height && null === $width) {
            if (
                !\is_array($source[0]) || ($width = \count($source[0])) === 0 ||
                !\is_float($source[0][0]) && !\is_int($source[0][0])
            ) {
                throw new \InvalidArgumentException();
            }

            $m = new FloatMatrix($len, $width);

            $m->data = new Vector();
            $m->data->allocate($len * $width);

            for ($i = 0; $i < $len; $i++) {
                if (\count($source[$i]) !== $width) {
                    throw new \InvalidArgumentException();
                }
                $m->data->push(...$source[$i]);
            }
            $m->data->apply(function ($x) { return (float)$x; });

            return $m;
        } else {
            throw new \InvalidArgumentException();
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // FloatMatrix methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param int $i
     * @param int $j
     * @return float
     */
    public function get(int $i, int $j) : float
    {
        return $this->data[$i * $this->width + $j];
    }

    /**
     * @param float $value
     * @param int $i
     * @param int $j
     */
    public function set(float $value, int $i, int $j)
    {
        $this->data[$i * $this->width + $j] = $value;
    }

    /**
     * @param Matrix $m
     * @param (null|int|int[])[] $sliceSpec
     */
    public function setSlice(Matrix $m, array $sliceSpec)
    {
        if (isAssociativeArray($sliceSpec) || \count($sliceSpec) !== 2) {
            throw new ShapeMismatchException();
        }

        list($sliceHeight, $sliceWidth) = $this->getNormalizedSliceSpec($sliceSpec);

        if (
            1 + $sliceWidth[1]  - $sliceWidth[0]  !== $this->width ||
            1 + $sliceHeight[1] - $sliceHeight[0] !== $this->height
        ) {
            throw new ShapeMismatchException();
        }

        $dataIndex = 0;
        for ($i = $sliceHeight[0]; $i <= $sliceHeight[1]; $i++) {
            for ($j = $sliceWidth[0]; $j <= $sliceWidth[1]; $j++) {
                $this->data[$i*$this->width + $j] = (float)$m->data[$dataIndex++];
            }
        }
    }

    /**
     * @param int[]|array[] $source
     * @param (null|int|int[])[] $sliceSpec
     * @return void
     */
    public function setArrayAsSlice(array $source, array $sliceSpec)
    {
        if (isAssociativeArray($sliceSpec) || \count($sliceSpec) !== 2) {
            throw new ShapeMismatchException();
        }

        list($slice1stDim, $slice2ndDim) = $this->getNormalizedSliceSpec($sliceSpec);
        $sliceHeight = 1 + $slice1stDim[1] - $slice1stDim[0];
        $sliceWidth  = 1 + $slice2ndDim[1] - $slice2ndDim[0];

        if (
            $slice2ndDim[0] < 0 || $slice2ndDim[1] >= $this->width ||
            $slice1stDim[0] < 0 || $slice1stDim[1] >= $this->height  ||
            $sliceHeight !== $this->height || $sliceWidth  !== $this->width
        ) {
            throw new ShapeMismatchException();
        }

        if (\is_float($source[0]) || \is_int($source[0])) {
            if (\count($source) !== $sliceHeight * $sliceWidth) {
                throw new ShapeMismatchException();
            }

            $dataIndex = 0;
            for ($i = $slice1stDim[0]; $i <= $slice1stDim[1]; $i++) {
                for ($j = $slice2ndDim[0]; $j <= $slice2ndDim[1]; $j++) {
                    $this->data[$i*$this->width + $j] = (float)$source[$dataIndex++];
                }
            }
        } elseif (\is_array($source[0])) {
            if (\count($source) !== $sliceHeight || \count($source[0]) !== $sliceWidth) {
                throw new ShapeMismatchException();
            }

            for ($i = $slice1stDim[0], $ix=0; $i <= $slice1stDim[1]; $i++, $ix++) {
                for ($j = $slice2ndDim[0], $jx=0; $j <= $slice2ndDim[1]; $j++, $jx++) {
                    $this->data[$i*$this->width + $j] = (float)$source[$ix][$jx];
                }
            }
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param int|float $c
     * @return void
     */
    protected function initWithConstant($c = 0.0)
    {
        $this->data = new Vector(
            \array_fill(0, $this->height*$this>$this->width, (float)$c)
        );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Core PHP interfaces methods
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof IntMatrix) {
            $this->setSlice($value, $offset);
        } elseif (\is_array($value) && \count($value) > 0) {
            $this->setArrayAsSlice($value, $offset);
        } else {
            $this->set($value, ...$offset);
        }
    }
}
