<?php
declare(strict_types=1);


namespace SDS;


use Ds\Vector;

use SDS\Exceptions\ShapeMismatchException;
use function SDS\functions\ {
    array_iMul
};


final class FloatTensor extends Tensor
{
    // -----------------------------------------------------------------------------------------------------------------
    // Static factories:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @param \int[] $shape
     * @return FloatTensor
     */
    static public function zeros (array $shape) : FloatTensor
    {
        self::checkShape(...$shape);

        $t = new FloatTensor();
        $t->setShape(new Vector($shape));
        $t->initWithConstant(0.0);

        return $t;
    }

    /**
     * @param \int[] $shape
     * @return FloatTensor
     */
    static public function ones (array $shape) : FloatTensor
    {
        self::checkShape(...$shape);

        $t = new FloatTensor();
        $t->setShape(new Vector($shape));
        $t->initWithConstant(1.0);

        return $t;
    }

    /**
     * @param float $c
     * @param \int[] $shape
     * @return FloatTensor
     */
    static public function constant (float $c, array $shape) : FloatTensor
    {
        self::checkShape(...$shape);

        $t = new FloatTensor();
        $t->setShape(new Vector($shape));
        $t->initWithConstant($c);

        return $t;
    }

    static public function randomUniform(array $shape, float $maxL = 1.0, float $minL = 0.0) : FloatTensor
    {
        self::checkShape(...$shape);

        $t = new FloatTensor();
        $t->setShape(new Vector($shape));

        $dataSize = array_iMul(...$shape);
        $t->data = new Vector();
        $t->data->allocate($dataSize);

        $factor = ($maxL-$minL)/getrandmax();
        for ($i=0; $i<$dataSize; $i++) {
            $t->data->push(rand()*$factor + $minL);
        }

        return $t;
    }

    static public function randomNormal(array $shape, float $mu = 0.0, float $sigma = 1.0) : FloatTensor
    {
        self::checkShape(...$shape);

        $t = new FloatTensor();
        $t->setShape(new Vector($shape));

        $dataSize = array_iMul(...$shape);
        $t->data = new Vector();
        $t->data->allocate($dataSize);

        $divisor = getrandmax();
        $two_pi  = 2.0 * 3.14159265358979323846;

        // Box-Muller Transform
        for ($i=0; $i<$dataSize; $i++) {
            $u1 = rand() / $divisor;
            $u2 = rand() / $divisor;

            $z0 = $sigma * (sqrt(-2.0 * log($u1)) * cos($two_pi * $u2)) + $mu;
            $z1 = sqrt(-2.0 * log($u1)) * sin($two_pi * $u2);

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

    static public function fromArray(array $source, array $shape = null) : FloatTensor
    {
        if (null !== $shape) {
            return self::fromArrayWithForcedShape($shape, ...$source);
        } else {
            return self::fromArrayWithInferredShape($source);
        }
    }

    static protected function fromArrayWithForcedShape(array $shape, float ...$source) : FloatTensor
    {
        self::checkShape(...$shape);
        if (array_iMul(...$shape) !== count($source)) {
            throw new ShapeMismatchException();
        }

        $t = new FloatTensor();
        $t->setShape(new Vector($shape));
        $t->data = new Vector($source);

        return $t;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // \ArrayAccess methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param \int[] $offset The offset to retrieve.
     * @return \float|FloatTensor
     */
    public function offsetGet($offset)
    {
        try {
            return $this->get(...$offset);
        } catch (\TypeError $te) {
            return $this->slice($offset);
        }
    }

    public function get(int ...$offset) : float
    {
        return $this->data[$this->getInternalIndex(...$offset)];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param \int[] $offset The offset to assign the value to.
     * @param \float $value The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof Tensor) {
            $this->setSlice($value, $offset);
        } elseif (is_array($value) && count($value) > 0) {
            if (is_float($value[0]) || is_int($value[0])) {
                $this->setSlice(
                    FloatTensor::fromArray($value, self::getShapeFromSliceSpec($offset, true)->toArray()),
                    $offset
                );
            } else {
                $this->setSlice(
                    FloatTensor::fromArray($value),
                    $offset
                );
            }
        } else {
            $this->set($value, $offset);
        }
    }

    /**
     * @param \float $value
     * @param \int[] $offset
     */
    public function set(float $value, array $offset)
    {
        $this->data[$this->getInternalIndex(...$offset)] = $value;
    }

    public function setSlice(Tensor $t, array $sliceSpec)
    {
        $targetSliceShape = $this->getShapeFromSliceSpec($sliceSpec, true);

        if ($targetSliceShape->toArray() !== $t->shape->toArray()) {
            throw new ShapeMismatchException();
        }

        $dataIndex = 0;
        foreach ($this->getInternalSlicesToBeCopied($sliceSpec) as $sliceToBeCopied) {
            for ($i=$sliceToBeCopied[0]; $i <= $sliceToBeCopied[1]; $i++) {
                $this->data[$i] = (float)$t->data[$dataIndex++];
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Protected/Private methods:
    // -----------------------------------------------------------------------------------------------------------------
    protected function initWithConstant($c = 0.0)
    {
        $this->data = new Vector(
            array_fill(0, array_iMul(...$this->shape), (float)$c)
        );
    }
}
