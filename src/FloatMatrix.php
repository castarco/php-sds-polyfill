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
     * @param int $size
     * @param float $v
     * @param null|int $width
     * @return FloatMatrix
     */
    public static function eye(int $size, float $v = 1.0, int $width = null) : FloatMatrix
    {
        if ($size <= 0) {
            throw new \DomainException('Matrix size must be strictly positive');
        }

        $width = $width ?? $size;
        $data = new Vector(\array_fill(0, $size * $width, 0.0));

        $mSize = \min($width, $size) * $width;
        for ($i = 0; $i < $mSize; $i += $width + 1) {
            $data[$i] = $v;
        }

        $m = new FloatMatrix($size, $width);
        $m->data = $data;

        return $m;
    }

    /**
     * @param float[] ...$vec
     * @return FloatMatrix
     */
    public static function diagonal(float ...$vec) : FloatMatrix
    {
        $size = \count($vec);

        if ($size === 0) {
            throw new \DomainException('Matrix size must be strictly positive');
        }

        $data = new Vector(\array_fill(0, $size * $size, 0.0));

        $i = 0;
        foreach ($vec as $v) {
            $data[$i] = $v;
            $i += $size + 1;
        }

        $m = new FloatMatrix($size, $size);
        $m->data = $data;

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
            $m->data->apply(function ($x) : float { return (float)$x; });

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
            $m->data->apply(function ($x) : float { return (float)$x; });

            return $m;
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * Creates a Householder matrix using a vector.
     * The Householder matrix "reflects" vectors using a normal plane to the given vector.
     *
     * @param Matrix $a
     * @return FloatMatrix
     */
    public static function householder(Matrix $a) : FloatMatrix
    {
        if ($a->width !== 1) {
            throw new ShapeMismatchException('Only "vertical" vectors are allowed');
        }

        $aData = $a->data;
        $n = $aData->count();

        $aSqNorm = 0.0;
        foreach ($aData as $x) {
            $aSqNorm += $x*$x;
        }

        $a0 = $aData[0];
        $d = $a0 + \sqrt($aSqNorm) * ($a0 >= 0 ? 1 : -1);

        $v = $aData->map(function ($x) use ($d) {
            return $x / $d;
        });
        $v[0] = 1.0;

        $d2 = 0.0;
        foreach ($v as $x) {
            $d2 += $x*$x;
        }
        $d2 = 2. / $d2;

        $H = new FloatMatrix($n, $n);
        $hData = new Vector(\array_fill(0, $n * $n, 0.0));
        $H->data = $hData;

        for ($i = 0, $in = 0; $i < $n; $i++, $in += $n) {
            $hData[$in + $i] = 1.0;
            for ($j = 0; $j < $n; $j++) {
                $hData[$in + $j] -= $d2 * $v[$i] * $v[$j];
            }
        }

        return $H;
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
        if ($i < 0 || $i >= $this->height || $j < 0 || $j >= $this->width) {
            throw new \OutOfBoundsException();
        }

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
            throw new \OutOfRangeException();
        }

        list($sliceHeight, $sliceWidth) = $this->getNormalizedSliceSpec($sliceSpec);

        if (
            1 + $sliceWidth[1]  - $sliceWidth[0]  !== $m->width ||
            1 + $sliceHeight[1] - $sliceHeight[0] !== $m->height
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
            if (\count($source) !== $sliceHeight) {
                throw new ShapeMismatchException();
            }

            for ($i = $slice1stDim[0], $ix=0; $i <= $slice1stDim[1]; $i++, $ix++) {
                if (\count($source[$ix]) !== $sliceWidth) {
                    throw new ShapeMismatchException();
                }

                for ($j = $slice2ndDim[0], $jx=0; $j <= $slice2ndDim[1]; $j++, $jx++) {
                    $this->data[$i*$this->width + $j] = (float)$source[$ix][$jx];
                }
            }
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param bool $inPlace
     * @return FloatMatrix
     */
    public function neg(bool $inPlace = false) : FloatMatrix
    {
        $negM = $inPlace ? $this : clone $this;

        foreach ($negM->data as $i => $v) {
            $negM->data[$i] = -$negM->data[$i];
        }

        return $negM;
    }

    /**
     * @param Matrix $m
     * @param bool $inPlace
     * @return FloatMatrix
     */
    public function add(Matrix $m, bool $inPlace = false) : FloatMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $sumM = $inPlace ? $this : clone $this;
        foreach ($sumM->data as $i => $v) {
            $sumM->data[$i] += (float)$m->data[$i];
        }

        return $sumM;
    }

    /**
     * @param Matrix $m
     * @param bool $inPlace
     * @return FloatMatrix
     */
    public function sub(Matrix $m, bool $inPlace = false) : FloatMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $subM = $inPlace ? $this : clone $this;
        foreach ($subM->data as $i => $v) {
            $subM->data[$i] -= (float)$m->data[$i];
        }

        return $subM;
    }

    /**
     * @param Matrix $m
     * @param bool $inPlace
     * @return FloatMatrix
     */
    public function mul(Matrix $m, bool $inPlace = false) : FloatMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $mulM = $inPlace ? $this : clone $this;
        foreach ($mulM->data as $i => $v) {
            $mulM->data[$i] *= (float)$m->data[$i];
        }

        return $mulM;
    }

    /**
     * @param Matrix $t
     * @param bool $inPlace
     * @return FloatMatrix
     */
    public function div(Matrix $t, bool $inPlace = false) : FloatMatrix
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $divM = $inPlace ? $this : clone $this;
        foreach ($divM->data as $i => $v) {
            $divM->data[$i] /= $t->data[$i];
        }

        return $divM;
    }

    /**
     * @param Matrix $m
     * @param bool $inPlace
     * @return FloatMatrix
     */
    public function mod(Matrix $m, bool $inPlace = false) : FloatMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $modM = $inPlace ? $this : clone $this;
        foreach ($modM->data as $i => $v) {
            $modM->data[$i] = \fmod($modM->data[$i], $m->data[$i]);
        }

        return $modM;
    }

    /**
     * @param Matrix $m
     * @param bool $inPlace
     * @return FloatMatrix
     */
    public function pow(Matrix $m, bool $inPlace = false) : FloatMatrix
    {
        if ($m->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $powM = $inPlace ? $this : clone $this;
        foreach ($powM->data as $i => $v) {
            $powM->data[$i] = (float)\pow($powM->data[$i], $m->data[$i]);
        }

        return $powM;
    }

    /**
     * @param bool $inPlace
     * @param bool $toIntMatrix
     * @return Matrix|IntMatrix|FloatMatrix
     */
    public function round(bool $inPlace = false, $toIntMatrix = false) : Matrix
    {
        if ($toIntMatrix) {
            $roundM = new IntMatrix();
            $roundM->setShape($this->shape);
            $roundM->data = clone $this->data;

        } else {
            $roundM = $inPlace ? $this : clone $this;
        }

        $roundM->data->apply($toIntMatrix
            ? function ($x) : int { return (int)\round($x); }
            : 'round'
        );

        return $roundM;
    }

    /**
     * @param bool $inPlace
     * @param bool $toIntMatrix
     * @return Matrix|IntMatrix|FloatMatrix
     */
    public function ceil(bool $inPlace = false, $toIntMatrix = false) : Matrix
    {
        if ($toIntMatrix) {
            $ceilM = new IntMatrix();
            $ceilM->setShape($this->shape);
            $ceilM->data = clone $this->data;

        } else {
            $ceilM = $inPlace ? $this : clone $this;
        }

        $ceilM->data->apply($toIntMatrix
            ? function ($x) : int { return (int)\ceil($x); }
            : 'ceil'
        );

        return $ceilM;
    }

    /**
     * @param bool $inPlace
     * @param bool $toIntMatrix
     * @return Matrix|IntMatrix|FloatMatrix
     */
    public function floor(bool $inPlace = false, $toIntMatrix = false) : Matrix
    {
        if ($toIntMatrix) {
            $floorM = new IntMatrix();
            $floorM->setShape($this->shape);
            $floorM->data = clone $this->data;

        } else {
            $floorM = $inPlace ? $this : clone $this;
        }

        $floorM->data->apply($toIntMatrix
            ? function ($x) { return (int)\floor($x); }
            : 'floor'
        );

        return $floorM;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Core PHP interfaces methods
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @inheritdoc
     * @param int|float $value
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof Matrix) {
            $this->setSlice($value, $offset);
        } elseif (\is_array($value) && \count($value) > 0) {
            $this->setArrayAsSlice($value, $offset);
        } else {
            $this->set($value, ...$offset);
        }
    }
}
