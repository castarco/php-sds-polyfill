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
            throw new \OutOfBoundsException();
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
            throw new \OutOfBoundsException();
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
            throw new \OutOfRangeException();
        }

        list($slice1stDim, $slice2ndDim) = $this->getNormalizedSliceSpec($sliceSpec);

        if (
            $slice2ndDim[0] < 0 || $slice2ndDim[1] >= $this->width  ||
            $slice1stDim[0] < 0 || $slice1stDim[1] >= $this->height ||
            1 + $slice2ndDim[1] - $slice2ndDim[0] !== $m->width ||
            1 + $slice1stDim[1] - $slice1stDim[0] !== $m->height
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
            throw new \OutOfRangeException();
        }

        list($slice1stDim, $slice2ndDim) = $this->getNormalizedSliceSpec($sliceSpec);
        $sliceHeight = 1 + $slice1stDim[1] - $slice1stDim[0];
        $sliceWidth  = 1 + $slice2ndDim[1] - $slice2ndDim[0];

        if (
            $slice2ndDim[0] < 0 || $slice2ndDim[1] >= $this->width ||
            $slice1stDim[0] < 0 || $slice1stDim[1] >= $this->height
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
            if (\count($source) !== $sliceHeight) {
                throw new ShapeMismatchException();
            }

            for ($i = $slice1stDim[0], $ix=0; $i <= $slice1stDim[1]; $i++, $ix++) {
                if (\count($source[$ix]) !== $sliceWidth) {
                    throw new ShapeMismatchException();
                }

                for ($j = $slice2ndDim[0], $jx=0; $j <= $slice2ndDim[1]; $j++, $jx++) {
                    $this->data[$i*$this->width + $j] = (int)$source[$ix][$jx];
                }
            }
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param bool $inPlace
     * @return IntMatrix
     */
    public function neg(bool $inPlace = false) : IntMatrix
    {
        $negT = $inPlace ? $this : clone $this;

        // TODO: Check if Vector::apply can be faster
        foreach ($negT->data as $i => $v) {
            $negT->data[$i] = -$negT->data[$i];
        }

        return $negT;
    }

    /**
     * @param IntMatrix $m
     * @param bool $inPlace
     * @return IntMatrix
     */
    public function add(IntMatrix $m, bool $inPlace = false) : IntMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $sumM = $inPlace ? $this : clone $this;
        foreach ($sumM->data as $i => $v) {
            $sumM->data[$i] += $m->data[$i];
        }

        return $sumM;
    }

    /**
     * @param IntMatrix $m
     * @param bool $inPlace
     * @return IntMatrix
     */
    public function sub(IntMatrix $m, bool $inPlace = false) : IntMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $subM = $inPlace ? $this : clone $this;
        foreach ($subM->data as $i => $v) {
            $subM->data[$i] -= $m->data[$i];
        }

        return $subM;
    }

    /**
     * @param IntMatrix $m
     * @param bool $inPlace
     * @return IntMatrix
     */
    public function mul(IntMatrix $m, bool $inPlace = false) : IntMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $mulT = $inPlace ? $this : clone $this;
        foreach ($mulT->data as $i => $v) {
            $mulT->data[$i] *= $m->data[$i];
        }

        return $mulT;
    }

    /**
     * @param IntMatrix $m
     * @param bool $inPlace
     * @return IntMatrix
     */
    public function div(IntMatrix $m, bool $inPlace = false) : IntMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $divM = $inPlace ? $this : clone $this;
        foreach ($divM->data as $i => $v) {
            $divM->data[$i] = \intdiv($v, $m->data[$i]);
        }

        return $divM;
    }

    /**
     * @param IntMatrix $m
     * @param bool $inPlace
     * @return IntMatrix
     */
    public function mod(IntMatrix $m, bool $inPlace = false) : IntMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $modM = $inPlace ? $this : clone $this;
        foreach ($modM->data as $i => $v) {
            $modM->data[$i] %= $m->data[$i];
        }

        return $modM;
    }

    /**
     * @param IntMatrix $m
     * @param bool $inPlace
     * @return IntMatrix
     */
    public function pow(IntMatrix $m, bool $inPlace = false) : IntMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $powM = $inPlace ? $this : clone $this;
        foreach ($powM->data as $i => $v) {
            $powM->data[$i] = (int)\pow($powM->data[$i], $m->data[$i]);
        }

        return $powM;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Core PHP interfaces methods
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @inheritdoc
     * @param int $value
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
