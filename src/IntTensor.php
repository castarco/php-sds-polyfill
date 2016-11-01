<?php
declare(strict_types=1);


namespace SDS;


use Ds\Vector;

use SDS\Exceptions\ShapeMismatchException;
use function SDS\functions\ {
    array_iMul,
    randBinomial
};


final class IntTensor extends Tensor
{
    // -----------------------------------------------------------------------------------------------------------------
    // Static factories:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param int[] $shape
     * @return IntTensor
     */
    public static function zeros (array $shape) : IntTensor
    {
        return self::constant(0, $shape);
    }

    /**
     * @param int[] $shape
     * @return IntTensor
     */
    public static function ones (array $shape) : IntTensor
    {
        return self::constant(1, $shape);
    }

    /**
     * @param int $c
     * @param int[] $shape
     * @return IntTensor
     */
    public static function constant (int $c, array $shape) : IntTensor
    {
        self::checkShape(...$shape);

        $t = new IntTensor();
        $t->setShape($shape);
        $t->initWithConstant($c);

        return $t;
    }

    /**
     * @param int[] $shape
     * @param int $maxL
     * @param int $minL
     * @return IntTensor
     */
    public static function randomUniform(array $shape, int $maxL = 1, int $minL = 0) : IntTensor
    {
        self::checkShape(...$shape);

        $t = new IntTensor();
        $t->setShape($shape);

        $dataSize = array_iMul(...$shape);
        $t->data = new Vector();
        $t->data->allocate($dataSize);

        for ($i=0; $i<$dataSize; $i++) {
            $t->data->push(rand($minL, $maxL));
        }

        return $t;
    }

    /**
     * @param int[] $shape
     * @param int $n
     * @return IntTensor
     */
    public static function randomBinomial(array $shape, int $n) : IntTensor
    {
        self::checkShape(...$shape);

        $t = new IntTensor();
        $t->setShape($shape);

        $dataSize = array_iMul(...$shape);
        $t->data = new Vector();
        $t->data->allocate($dataSize);

        for ($i=0; $i<$dataSize; $i++) {
            $t->data->push(randBinomial($n));
        }

        return $t;
    }

    /**
     * @param int[]|array[] $source
     * @param int[]|null $shape
     * @return IntTensor
     */
    public static function fromArray(array $source, array $shape = null) : IntTensor
    {
        if (null !== $shape) {
            return static::fromArrayWithForcedShape($shape, ...$source);
        } else {
            return static::fromArrayWithInferredShape($source);
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // FloatTensor methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param int[] ...$offset
     * @return int
     */
    public function get(int ...$offset) : int
    {
        return $this->data[$this->getInternalIndex(...$offset)];
    }

    /**
     * @param int $value
     * @param int[] $offset
     */
    public function set(int $value, array $offset)
    {
        $this->data[$this->getInternalIndex(...$offset)] = $value;
    }

    /**
     * @param IntTensor $t
     * @param (null|int|int[])[] $sliceSpec
     */
    public function setSlice(IntTensor $t, array $sliceSpec)
    {
        $targetSliceShape = $this->getShapeFromSliceSpec($sliceSpec, true);

        if ($targetSliceShape !== $t->shape) {
            throw new ShapeMismatchException();
        }

        $dataIndex = 0;
        foreach ($this->getInternalSlicesToBeCopied($sliceSpec) as $sliceToBeCopied) {
            for ($i=$sliceToBeCopied[0]; $i <= $sliceToBeCopied[1]; $i++) {
                $this->data[$i] = $t->data[$dataIndex++];
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

        if (\is_int($source[0])) {
            if (\count($source) !== array_iMul(...$targetSliceShape)) {
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
                $this->data[$i] = (int)$source[$dataIndex++];
            }
        }
    }

    /**
     * @param bool $inPlace
     * @return IntTensor
     */
    public function neg(bool $inPlace = false) : IntTensor
    {
        $negT = $inPlace ? $this : clone $this;

        // TODO: Check if Vector::apply can be faster
        foreach ($negT->data as $i => $v) {
            $negT->data[$i] = -$negT->data[$i];
        }

        return $negT;
    }

    /**
     * @param IntTensor $t
     * @param bool $inPlace
     * @return IntTensor
     */
    public function add(IntTensor $t, bool $inPlace = false) : IntTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $sumT = $inPlace ? $this : clone $this;
        foreach ($sumT->data as $i => $v) {
            $sumT->data[$i] += $t->data[$i];
        }

        return $sumT;
    }

    /**
     * @param IntTensor $t
     * @param bool $inPlace
     * @return IntTensor
     */
    public function sub(IntTensor $t, bool $inPlace = false) : IntTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $subT = $inPlace ? $this : clone $this;
        foreach ($subT->data as $i => $v) {
            $subT->data[$i] -= $t->data[$i];
        }

        return $subT;
    }

    /**
     * @param IntTensor $t
     * @param bool $inPlace
     * @return IntTensor
     */
    public function mul(IntTensor $t, bool $inPlace = false) : IntTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $mulT = $inPlace ? $this : clone $this;
        foreach ($mulT->data as $i => $v) {
            $mulT->data[$i] *= $t->data[$i];
        }

        return $mulT;
    }

    /**
     * @param IntTensor $t
     * @param bool $inPlace
     * @return IntTensor
     */
    public function div(IntTensor $t, bool $inPlace = false) : IntTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $divT = $inPlace ? $this : clone $this;
        foreach ($divT->data as $i => $v) {
            $divT->data[$i] = \intdiv($v, $t->data[$i]);
        }

        return $divT;
    }

    /**
     * @param IntTensor $t
     * @param bool $inPlace
     * @return IntTensor
     */
    public function mod(IntTensor $t, bool $inPlace = false) : IntTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $modT = $inPlace ? $this : clone $this;
        foreach ($modT->data as $i => $v) {
            $modT->data[$i] %= $t->data[$i];
        }

        return $modT;
    }

    /**
     * @param IntTensor $t
     * @param bool $inPlace
     * @return IntTensor
     */
    public function pow(IntTensor $t, bool $inPlace = false) : IntTensor
    {
        if ($t->shape !== $this->shape) {
            throw new ShapeMismatchException();
        }

        $modT = $inPlace ? $this : clone $this;
        foreach ($modT->data as $i => $v) {
            $modT->data[$i] = (int)\pow($modT->data[$i], $t->data[$i]);
        }

        return $modT;
    }

    /**
     * @param int $c
     */
    protected function initWithConstant($c = 0)
    {
        $this->data = new Vector(
            array_fill(0, array_iMul(...$this->shape), (int)$c)
        );
    }

    /**
     * @param int[] $shape
     * @param int[] ...$source
     * @return IntTensor
     */
    protected static function fromArrayWithForcedShape(array $shape, int ...$source) : IntTensor
    {
        self::checkShape(...$shape);
        if (array_iMul(...$shape) !== count($source)) {
            throw new ShapeMismatchException();
        }

        $t = new IntTensor();
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
     * @param int $value The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof IntTensor) {
            $this->setSlice($value, $offset);
        } elseif (is_array($value) && count($value) > 0) {
            $this->setArrayAsSlice($value, $offset);
        } else {
            $this->set($value, $offset);
        }
    }
}
