<?php
declare(strict_types=1);


namespace SDS;


use Ds\Vector;

use SDS\Exceptions\ShapeMismatchException;
use function SDS\functions\ {
    array_iMul
};


final class IntTensor extends Tensor
{
    // -----------------------------------------------------------------------------------------------------------------
    // Static factories:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param \int[] $shape
     * @return IntTensor
     */
    static public function zeros (array $shape) : IntTensor
    {
        self::checkShape(...$shape);

        $t = new IntTensor();
        $t->setShape(new Vector($shape));
        $t->initWithConstant(0);

        return $t;
    }

    /**
     * @param \int[] $shape
     * @return IntTensor
     */
    static public function ones (array $shape) : IntTensor
    {
        self::checkShape(...$shape);

        $t = new IntTensor();
        $t->setShape(new Vector($shape));
        $t->initWithConstant(1);

        return $t;
    }

    /**
     * @param int $c
     * @param \int[] $shape
     * @return IntTensor
     */
    static public function constant (int $c, array $shape) : IntTensor
    {
        self::checkShape(...$shape);

        $t = new IntTensor();
        $t->setShape(new Vector($shape));
        $t->initWithConstant($c);

        return $t;
    }

    static public function fromArray(array $source, array $shape = null) : IntTensor
    {
        if (null !== $shape) {
            return self::fromArrayWithForcedShape($shape, ...$source);
        } else {
            return self::fromArrayWithInferredShape($source);
        }
    }

    static private function fromArrayWithForcedShape(array $shape, int ...$source) : IntTensor
    {
        self::checkShape(...$shape);
        if (array_iMul(...$shape) !== count($source)) {
            throw new ShapeMismatchException();
        }

        $t = new IntTensor();
        $t->setShape(new Vector($shape));
        $t->data = new Vector($source);

        return $t;
    }

    static private function fromArrayWithInferredShape(array $source) : IntTensor
    {
        $shape = [];
        $data = $source;

        for ($i=$source; is_array($i); $i=$i[0]) {
            $shape[] = count($i);
            if (is_array($i[0])) {
                $data = self::flattenNestedArray($data, count($i));
            }
        }

        return self::fromArrayWithForcedShape($shape, ...$data);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // \ArrayAccess methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param \int[] $offset The offset to retrieve.
     * @return \int|IntTensor
     */
    public function offsetGet($offset)
    {
        try {
            return $this->get(...$offset);
        } catch (\TypeError $te) {
            return $this->slice($offset);
        }
    }

    public function get(int ...$offset) : int
    {
        return $this->data[$this->getInternalIndex(...$offset)];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param \int[] $offset The offset to assign the value to.
     * @param \int $value The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof IntTensor) {
            $this->setSlice($value, $offset);
        } else {
            $this->set($value, $offset);
        }
    }

    /**
     * @param \int $value
     * @param \int[] $offset
     */
    public function set(int $value, array $offset)
    {
        $this->data[$this->getInternalIndex(...$offset)] = $value;
    }

    public function setSlice(IntTensor $t, array $sliceSpec)
    {
        $targetSliceShape = $this->getShapeFromSliceSpec($sliceSpec, true);

        if ($targetSliceShape->toArray() !== $t->shape->toArray()) {
            throw new ShapeMismatchException();
        }

        $dataIndex = 0;
        foreach ($this->getInternalSlicesToBeCopied($sliceSpec) as $sliceToBeCopied) {
            for ($i=$sliceToBeCopied[0]; $i <= $sliceToBeCopied[1]; $i++) {
                $this->data[$i] = $t->data[$dataIndex++];
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Protected/Private methods:
    // -----------------------------------------------------------------------------------------------------------------
    protected function initWithConstant($c = 0)
    {
        $this->data = new Vector(
            array_fill(0, array_iMul(...$this->shape), (int)$c)
        );
    }
}
