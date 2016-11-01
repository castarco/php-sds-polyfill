<?php
declare(strict_types=1);


namespace SDS;


use Ds\Vector;
use SDS\Exceptions\ShapeMismatchException;
use function SDS\functions\{
    isAssociativeArray,
    randBinomial
};


final class IntMatrix extends Matrix
{
    // -----------------------------------------------------------------------------------------------------------------
    // Static factories:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param int $height
     * @param int $width
     * @return IntMatrix
     */
    public static function zeros (int $height, int $width) : IntMatrix
    {
        return self::constant(0, $height, $width);
    }

    /**
     * @param int $height
     * @param int $width
     * @return IntMatrix
     */
    public static function ones (int $height, int $width) : IntMatrix
    {
        return self::constant(1, $height, $width);
    }

    /**
     * @param int $c
     * @param int $height
     * @param int $width
     * @return IntMatrix
     */
    public static function constant (int $c, int $height, int $width) : IntMatrix
    {
        $m = new IntMatrix($height, $width);
        $m->data = new Vector(\array_fill(0, $height * $width, $c));

        return $m;
    }

    /**
     * @param int $height
     * @param int $width
     * @param int $maxL
     * @param int $minL
     * @return IntMatrix
     */
    public static function randomUniform(int $height, int $width, int $maxL = 1, int $minL = 0) : IntMatrix
    {
        $m = new IntMatrix($height, $width);
        $m->data = new Vector();

        $dataSize = $height * $width;
        $m->data->allocate($dataSize);

        for ($i=0; $i<$dataSize; $i++) {
            $m->data->push(rand($minL, $maxL));
        }

        return $m;
    }

    /**
     * @param int $height
     * @param int $width
     * @param int $n
     * @return IntMatrix
     */
    public static function randomBinomial(int $height, int $width, int $n) : IntMatrix
    {
        $m = new IntMatrix($height, $width);
        $m->data = new Vector();

        $dataSize = $height * $width;
        $m->data->allocate($dataSize);

        for ($i=0; $i<$dataSize; $i++) {
            $m->data->push(randBinomial($n));
        }

        return $m;
    }

    /**
     * @param int[]|array[] $source
     * @param null|int $height
     * @param null|int $width
     * @return IntMatrix
     */
    public static function fromArray(array $source, int $height = null, int $width = null) : IntMatrix
    {
        if (($len = \count($source)) === 0) {
            throw new \InvalidArgumentException();
        }

        if (null !== $height && null !== $width && \is_int($source[0])) {

            if ($height * $width !== $len) {
                throw new \InvalidArgumentException();
            }

            $m = new IntMatrix($height, $width);

            $m->data = new Vector($source);
            $m->data->apply(function ($x) { return (int)$x; });

            return $m;

        } elseif (null === $height && null === $width) {
            if (!\is_array($source[0]) || ($width = \count($source[0])) === 0 || !\is_int($source[0][0])) {
                throw new \InvalidArgumentException();
            }

            $m = new IntMatrix($len, $width);

            $m->data = new Vector();
            $m->data->allocate($len * $width);

            for ($i = 0; $i < $len; $i++) {
                if (\count($source[$i]) !== $width) {
                    throw new \InvalidArgumentException();
                }
                $m->data->push(...$source[$i]);
            }
            $m->data->apply(function ($x) { return (int)$x; });

            return $m;
        } else {
            throw new \InvalidArgumentException();
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // IntMatrix methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param int $i
     * @param int $j
     * @return int
     */
    public function get(int $i, int $j) : int
    {
        if ($i < 0 || $i >= $this->height || $j < 0 || $j >= $this->width) {
            throw new \InvalidArgumentException();
        }

        return $this->data[$i * $this->width + $j];
    }

    /**
     * @param int $value
     * @param int $i
     * @param int $j
     */
    public function set(int $value, int $i, int $j)
    {
        if ($i < 0 || $i >= $this->height || $j < 0 || $j >= $this->width) {
            throw new \InvalidArgumentException();
        }

        $this->data[$i * $this->width + $j] = $value;
    }

    /**
     * @param IntMatrix $m
     * @param (null|int|int[])[] $sliceSpec
     */
    public function setSlice(IntMatrix $m, array $sliceSpec)
    {
        if (isAssociativeArray($sliceSpec) || \count($sliceSpec) !== 2) {
            throw new ShapeMismatchException();
        }

        list($slice1stDim, $slice2ndDim) = $this->getNormalizedSliceSpec($sliceSpec);

        if (
            $slice2ndDim[0] < 0 || $slice2ndDim[1] >= $this->width ||
            $slice1stDim[0] < 0 || $slice1stDim[1] >= $this->height  ||
            1 + $slice2ndDim[1] - $slice2ndDim[0] !== $this->width ||
            1 + $slice1stDim[1] - $slice1stDim[0] !== $this->height
        ) {
            throw new ShapeMismatchException();
        }

        $dataIndex = 0;
        for ($i = $slice1stDim[0]; $i <= $slice1stDim[1]; $i++) {
            for ($j = $slice2ndDim[0]; $j <= $slice2ndDim[1]; $j++) {
                $this->data[$i*$this->width + $j] = $m->data[$dataIndex++];
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

        if (\is_int($source[0])) {
            if (\count($source) !== $sliceHeight * $sliceWidth) {
                throw new ShapeMismatchException();
            }

            $dataIndex = 0;
            for ($i = $slice1stDim[0]; $i <= $slice1stDim[1]; $i++) {
                for ($j = $slice2ndDim[0]; $j <= $slice2ndDim[1]; $j++) {
                    $this->data[$i*$this->width + $j] = (int)$source[$dataIndex++];
                }
            }
        } elseif (\is_array($source[0])) {
            if (\count($source) !== $sliceHeight || \count($source[0]) !== $sliceWidth) {
                throw new ShapeMismatchException();
            }

            for ($i = $slice1stDim[0], $ix=0; $i <= $slice1stDim[1]; $i++, $ix++) {
                for ($j = $slice2ndDim[0], $jx=0; $j <= $slice2ndDim[1]; $j++, $jx++) {
                    $this->data[$i*$this->width + $j] = (int)$source[$ix][$jx];
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
    protected function initWithConstant($c = 0)
    {
        $this->data = new Vector(
            array_fill(0, $this->height * $this->width, (int)$c)
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
        } elseif (is_array($value) && count($value) > 0) {
            $this->setArrayAsSlice($value, $offset);
        } else {
            $this->set($value, ...$offset);
        }
    }
}
