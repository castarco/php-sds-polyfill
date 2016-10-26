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
        self::checkShape(...$shape);

        $t = new IntTensor();
        $t->setShape($shape);
        $t->initWithConstant(0);

        return $t;
    }

    /**
     * @param int[] $shape
     * @return IntTensor
     */
    public static function ones (array $shape) : IntTensor
    {
        self::checkShape(...$shape);

        $t = new IntTensor();
        $t->setShape($shape);
        $t->initWithConstant(1);

        return $t;
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
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param int[] $offset The offset to retrieve.
     * @return int|IntTensor
     */
    public function offsetGet($offset)
    {
        try {
            return $this->get(...$offset);
        } catch (\TypeError $te) {
            return $this->slice($offset);
        }
    }

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
            if (is_int($value[0])) {
                $this->setSlice(
                    IntTensor::fromArray($value, self::getShapeFromSliceSpec($offset, true)),
                    $offset
                );
            } else {
                $this->setSlice(
                    IntTensor::fromArray($value),
                    $offset
                );
            }
        } else {
            $this->set($value, $offset);
        }
    }
}
