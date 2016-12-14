<?php
declare(strict_types=1);


namespace SDS;


use Ds\Vector;

use SDS\Exceptions\ShapeMismatchException;


final class FloatTensor extends Tensor
{
    // -----------------------------------------------------------------------------------------------------------------
    // Static factories:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param int[] $shape
     * @return FloatTensor
     */
    public static function zeros (array $shape) : FloatTensor
    {
        return self::constant(0.0, $shape);
    }

    /**
     * @param int[] $shape
     * @return FloatTensor
     */
    public static function ones (array $shape) : FloatTensor
    {
        return self::constant(1.0, $shape);
    }

    /**
     * @param float $c
     * @param int[] $shape
     * @return FloatTensor
     */
    public static function constant (float $c, array $shape) : FloatTensor
    {
        self::checkShape(...$shape);

        $t = new FloatTensor();
        $t->setShape($shape);
        $t->initWithConstant($c);

        return $t;
    }

    /**
     * @param int[] $shape
     * @param float $maxL
     * @param float $minL
     * @return FloatTensor
     */
    public static function randomUniform(array $shape, float $maxL = 1.0, float $minL = 0.0) : FloatTensor
    {
        self::checkShape(...$shape);

        $t = new FloatTensor();
        $t->setShape($shape);

        $dataSize = (int)\array_product($shape);
        $t->data = new Vector();
        $t->data->allocate($dataSize);

        $factor = ($maxL-$minL)/\getrandmax();
        for ($i=0; $i<$dataSize; $i++) {
            $t->data->push(\rand()*$factor + $minL);
        }

        return $t;
    }

    /**
     * @param int[] $shape
     * @param float $mu
     * @param float $sigma
     * @return FloatTensor
     */
    public static function randomNormal(array $shape, float $mu = 0.0, float $sigma = 1.0) : FloatTensor
    {
        self::checkShape(...$shape);

        $t = new FloatTensor();
        $t->setShape($shape);

        $dataSize = (int)\array_product($shape);
        $t->data = new Vector();
        $t->data->allocate($dataSize);

        $divisor = \getrandmax();
        $two_pi  = 2.0 * 3.14159265358979323846;

        // Box-Muller Transform
        for ($i=0; $i<$dataSize; $i++) {
            $u1 = \rand() / $divisor;
            $u2 = \rand() / $divisor;

            $z0 = $sigma * (\sqrt(-2.0 * \log($u1)) * \cos($two_pi * $u2)) + $mu;
            $z1 = \sqrt(-2.0 * \log($u1)) * \sin($two_pi * $u2);

            $t->data->push($z0);
            $i++;

            if ($i < $dataSize) {
                // We do here the product to avoid doing it when it's not useful.
                $t->data->push($sigma * $z1 + $mu);
            } else {
                // To avoid the extra comparison in the for loop "header".
                break;
            }
        }

        return $t;
    }

    /**
     * @param float[]|array[] $source
     * @param null|int[] $shape
     * @return FloatTensor
     */
    public static function fromArray(array $source, array $shape = null) : FloatTensor
    {
        if (null !== $shape) {
            return self::fromArrayWithForcedShape($shape, ...$source);
        } else {
            return self::fromArrayWithInferredShape($source);
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // FloatTensor methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param int[] ...$offset
     * @return float
     */
    public function get(int ...$offset) : float
    {
        return $this->data[$this->getInternalIndex(...$offset)];
    }

    /**
     * @param float $value
     * @param int[] $offset
     */
    public function set(float $value, array $offset)
    {
        $this->data[$this->getInternalIndex(...$offset)] = $value;
    }

    /**
     * @param Tensor $t
     * @param (null|int|int[])[] $sliceSpec
     */
    public function setSlice(Tensor $t, array $sliceSpec)
    {
        $targetSliceShape = $this->getShapeFromSliceSpec($sliceSpec, true);

        if ($targetSliceShape !== $t->shape) {
            throw new ShapeMismatchException();
        }

        $dataIndex = 0;
        foreach ($this->getInternalSlicesToBeCopied($sliceSpec) as $sliceToBeCopied) {
            for ($i=$sliceToBeCopied[0]; $i <= $sliceToBeCopied[1]; $i++) {
                $this->data[$i] = (float)$t->data[$dataIndex++];
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
        $targetSliceShape = $this->getShapeFromSliceSpec($sliceSpec, true);

        if (\is_float($source[0]) || \is_int($source[0])) {
            if (\count($source) !== \array_product($targetSliceShape)) {
                throw new ShapeMismatchException();
            }
        } else {
            list($srcShape, $source) = self::inferShapeAndExtractData($source);

            if ($srcShape !== $targetSliceShape) {
                throw new ShapeMismatchException();
            }
        }

        $dataIndex = 0;
        foreach ($this->getInternalSlicesToBeCopied($sliceSpec) as $sliceToBeCopied) {
            for ($i=$sliceToBeCopied[0]; $i <= $sliceToBeCopied[1]; $i++) {
                $this->data[$i] = (float)$source[$dataIndex++];
            }
        }
    }

    /**
     * @param bool $inPlace
     * @return FloatTensor
     */
    public function neg(bool $inPlace = false) : FloatTensor
    {
        $sumT = $inPlace ? $this : clone $this;

        foreach ($sumT->data as $i => $v) {
            $sumT->data[$i] = -$sumT->data[$i];
        }

        return $sumT;
    }

    /**
     * @param Tensor $t
     * @param bool $inPlace
     * @return FloatTensor
     */
    public function add(Tensor $t, bool $inPlace = false) : FloatTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $sumT = $inPlace ? $this : clone $this;
        foreach ($sumT->data as $i => $v) {
            $sumT->data[$i] += (float)$t->data[$i];
        }

        return $sumT;
    }

    /**
     * @param Tensor $t
     * @param bool $inPlace
     * @return FloatTensor
     */
    public function sub(Tensor $t, bool $inPlace = false) : FloatTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $subT = $inPlace ? $this : clone $this;
        foreach ($subT->data as $i => $v) {
            $subT->data[$i] -= (float)$t->data[$i];
        }

        return $subT;
    }

    /**
     * @param Tensor $t
     * @param bool $inPlace
     * @return FloatTensor
     */
    public function mul(Tensor $t, bool $inPlace = false) : FloatTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $mulT = $inPlace ? $this : clone $this;
        foreach ($mulT->data as $i => $v) {
            $mulT->data[$i] *= (float)$t->data[$i];
        }

        return $mulT;
    }

    /**
     * @param Tensor $t
     * @param bool $inPlace
     * @return FloatTensor
     */
    public function div(Tensor $t, bool $inPlace = false) : FloatTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $divT = $inPlace ? $this : clone $this;
        foreach ($divT->data as $i => $v) {
            $divT->data[$i] /= $t->data[$i];
        }

        return $divT;
    }

    /**
     * @param Tensor $t
     * @param bool $inPlace
     * @return FloatTensor
     */
    public function mod(Tensor $t, bool $inPlace = false) : FloatTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $modT = $inPlace ? $this : clone $this;
        foreach ($modT->data as $i => $v) {
            $modT->data[$i] = \fmod($modT->data[$i], $t->data[$i]);
        }

        return $modT;
    }

    /**
     * @param Tensor $t
     * @param bool $inPlace
     * @return FloatTensor
     */
    public function pow(Tensor $t, bool $inPlace = false) : FloatTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $powT = $inPlace ? $this : clone $this;
        foreach ($powT->data as $i => $v) {
            $powT->data[$i] = (float)\pow($powT->data[$i], $t->data[$i]);
        }

        return $powT;
    }

    /**
     * @param bool $inPlace
     * @param bool $toIntTensor
     * @return Tensor|IntTensor|FloatTensor
     */
    public function round(bool $inPlace = false, $toIntTensor = false) : Tensor
    {
        if ($toIntTensor) {
            $roundT = new IntTensor();
            $roundT->setShape($this->shape);
            $roundT->data = clone $this->data;

        } else {
            $roundT = $inPlace ? $this : clone $this;
        }

        $roundT->data->apply(function ($x) { return (int)\round($x); });

        return $roundT;
    }

    /**
     * @param bool $inPlace
     * @param bool $toIntTensor
     * @return Tensor|IntTensor|FloatTensor
     */
    public function ceil(bool $inPlace = false, $toIntTensor = false) : Tensor
    {
        if ($toIntTensor) {
            $ceilT = new IntTensor();
            $ceilT->setShape($this->shape);
            $ceilT->data = clone $this->data;

        } else {
            $ceilT = $inPlace ? $this : clone $this;
        }

        $ceilT->data->apply(function ($x) { return (int)\ceil($x); });

        return $ceilT;
    }

    /**
     * @param bool $inPlace
     * @param bool $toIntTensor
     * @return Tensor|IntTensor|FloatTensor
     */
    public function floor(bool $inPlace = false, $toIntTensor = false) : Tensor
    {
        if ($toIntTensor) {
            $floorT = new IntTensor();
            $floorT->setShape($this->shape);
            $floorT->data = clone $this->data;

        } else {
            $floorT = $inPlace ? $this : clone $this;
        }

        $floorT->data->apply(function ($x) { return (int)\floor($x); });

        return $floorT;
    }

    /**
     * @param float $c
     */
    protected function initWithConstant($c = 0.0)
    {
        $this->data = new Vector(
            \array_fill(0, \array_product($this->shape), (float)$c)
        );
    }

    /**
     * @param float[] $source
     * @param int[] $shape
     * @return FloatTensor
     */
    protected static function fromArrayWithForcedShape(array $shape, float ...$source) : FloatTensor
    {
        self::checkShape(...$shape);
        if (\array_product($shape) !== \count($source)) {
            throw new ShapeMismatchException();
        }

        $t = new FloatTensor();
        $t->setShape($shape);
        $t->data = new Vector($source);

        return $t;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Core PHP interfaces methods
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param int[] $offset The offset to assign the value to.
     * @param float $value The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof Tensor) {
            $this->setSlice($value, $offset);
        } elseif (\is_array($value) && \count($value) > 0) {
            $this->setArrayAsSlice($value, $offset);
        } else {
            $this->set($value, $offset);
        }
    }
}
