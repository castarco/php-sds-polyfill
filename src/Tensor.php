<?php
declare(strict_types=1);


namespace SDS;


use Ds\{Hashable, Vector};

use SDS\Exceptions\ShapeMismatchException;
use function SDS\functions\ {
    isAssociativeArray,
    isPositive
};


abstract class Tensor implements \ArrayAccess, \Countable, \IteratorAggregate, Hashable
{
    /** @var \int[]|Vector */
    protected $shape;

    /** @var \int[]|Vector */
    protected $indexShifts;

    /** @var \int[]|\float[]|Vector */
    protected $data;


    // -----------------------------------------------------------------------------------------------------------------
    // Public methods:
    // -----------------------------------------------------------------------------------------------------------------
    public function __clone()
    {
        $this->shape = clone $this->shape;
        $this->data  = clone $this->data;
    }

    public function slice(array $sliceSpec, $keepRedundantDims=false) : Tensor
    {
        $slice = new static();
        $slice->setShape($this->getShapeFromSliceSpec($sliceSpec, $keepRedundantDims));
        $slice->initWithConstant(0);

        $dataIndex = 0;
        foreach ($this->getInternalSlicesToBeCopied($sliceSpec) as $sliceToBeCopied) {
            for ($i=$sliceToBeCopied[0]; $i <= $sliceToBeCopied[1]; $i++) {
                $slice->data[$dataIndex++] = $this->data[$i];
            }
        }

        return $slice;
    }

    /**
     * @return \int[]
     */
    public function getShape() : array
    {
        return $this->shape->toArray();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Abstract methods:
    // -----------------------------------------------------------------------------------------------------------------
    abstract public function get(int ...$offset);

    abstract protected function initWithConstant($c = 0);

    // -----------------------------------------------------------------------------------------------------------------
    // Ds\Hashable methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @inheritdoc
     */
    public function hash() : int
    {
        $mod = (2 << 61);

        $hash = (31       + \count($this->shape)) % $mod;
        $hash = (37*$hash + \count($this->data))  % $mod;

        foreach ($this->shape as $dimWidth) {
            $hash = (41*$hash + $dimWidth) % $mod;
        }
        foreach ($this->data as $cell) {
            $hash = (43*$hash + (int)\floor($cell)) % $mod;
        }

        return $hash;
    }

    /**
     * @inheritdoc
     */
    public function equals($obj) : bool
    {
        return (
            $obj === $this ||
            (
                \get_class($obj) === \get_class($this) &&
                $obj->shape->toArray() === $this->shape->toArray() &&
                $obj->data->toArray()  === $this->data->toArray()
            )
        );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // \ArrayAccess methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @inheritdoc
     */
    public function offsetExists($offset) : bool
    {
        try {
            return $this->_offsetExists(...$offset);
        } catch (\Throwable $t) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Not supported operation');
    }

    protected function _offsetExists(int ...$offset) : bool
    {
        if (\count($offset) !== \count($this->shape)) {
            return false;
        }

        foreach ($this->shape as $dimIndex => $dimWidth) {
            // We allow negative indexes
            if ($offset[$dimIndex] >= $dimWidth || $offset[$dimIndex] < -$dimWidth) {
                return false;
            }
        }

        return true;
    }

    protected function getInternalIndex (int ...$offset) : int
    {
        if (\count($offset) !== \count($this->shape)) {
            throw new ShapeMismatchException('Unexpected number of dimensions on the coordinates');
        }

        foreach ($offset as $dimIndex => $coordinate) {
            if ($coordinate < 0 || $coordinate >= $this->shape[$dimIndex]) {
                throw new ShapeMismatchException('The passed offset does not fit into the tensor\'s shape');
            }
        }

        return (int)\array_sum(
            \array_map(
                'SDS\functions\iMultiply',
                $offset,
                $this->indexShifts
            )
        );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // \Countable methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @inheritdoc
     */
    public function count() : int
    {
        return \count($this->data);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // \IteratorAggregate methods:
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @inheritdoc
     */
    public function getIterator() : \Traversable
    {
        return $this->data->getIterator();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Tensor's protected/private methods
    // -----------------------------------------------------------------------------------------------------------------
    protected function __construct() { }

    protected function setShape(Vector $shape)
    {
        $this->shape       = $shape;
        $this->indexShifts = \array_fill(0, count($this->shape), 0);

        $v = 1;
        for ($i=\count($shape)-1; $i >= 0; $i--) {
            $this->indexShifts[$i] = $v;
            $v *= $shape[$i];
        }
    }

    protected function getShapeFromSliceSpec(array $sliceSpec, bool $keepRedundantDims=false) : Vector
    {
        $this->checkSliceSpec($sliceSpec);

        $shape = [];

        foreach ($sliceSpec as $dimIndex => $sComp) {
            if (null === $sComp) {
                $shape[] = $this->shape[$dimIndex];
            } elseif ($keepRedundantDims && \is_int($sComp)) {
                $shape[] = 1;
            } elseif (\is_array($sComp)) {
                $shape[] = 1 + $sComp[1] - $sComp[0];
            }
        }

        return new Vector($shape);
    }

    protected function getInternalSlicesToBeCopied(array $sliceSpec) : array
    {
        $normalizedSliceSpec = $this->getNormalizedSliceSpec($sliceSpec);
        $internalSlices = [[0, \count($this->data)-1]];

        for ($i = 0; $i < \count($this->shape); $i++) {
            $tmpInternalSlices = [];

            foreach ($internalSlices as $internalSlice) {
                for ($j=$normalizedSliceSpec[$i][0]; $j <= $normalizedSliceSpec[$i][1]; $j++) {
                    $tmpInternalSlices[] = [
                        $internalSlice[0] + $this->indexShifts[$i] * $j,
                        $internalSlice[0] + $this->indexShifts[$i] * (1 + $j) - 1
                    ];
                }
            }

            $internalSlices = $tmpInternalSlices;
        }

        return $internalSlices;
    }

    private function checkSliceSpec(array $sliceSpec)
    {
        if (isAssociativeArray($sliceSpec) || \count($sliceSpec) !== \count($this->shape)) {
            throw new ShapeMismatchException();
        }

        $nValidSlice = \count(\array_filter($sliceSpec, function ($sComp, $dimIndex) : bool {
            return (
                null === $sComp ||

                \is_int($sComp) && $sComp > 0 && $sComp < $this->shape[$dimIndex] ||

                (
                    \is_array($sComp) && \count($sComp) === 2 &&
                    \is_int($sComp[0]) && $sComp[0] >= 0 &&
                    \is_int($sComp[1]) && $sComp[1] > $sComp[0] && $sComp[1] < $this->shape[$dimIndex]
                )
            );
        }, ARRAY_FILTER_USE_BOTH));

        if ($nValidSlice !== \count($this->shape)) {
            throw new ShapeMismatchException();
        }
    }

    private function getNormalizedSliceSpec(array $sliceSpec) : array
    {
        $normalizedSliceSpec = [];

        foreach ($sliceSpec as $dimIndex => $sComp) {
            if (null === $sComp) {
                $normalizedSliceSpec[] = [0, $this->shape[$dimIndex] - 1];
            } elseif (\is_int($sComp)) {
                $normalizedSliceSpec[] = [$sComp, $sComp];
            } else {
                $normalizedSliceSpec[] = $sComp;
            }
        }

        return $normalizedSliceSpec;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Tensor's static methods
    // -----------------------------------------------------------------------------------------------------------------
    static protected function checkShape(int ...$shape)
    {
        if (\count(\array_filter($shape, 'SDS\functions\isPositive')) < \count($shape)) {
            throw new \InvalidArgumentException('Shape dimensions must have a strictly positive width');
        }
    }

    static protected function flattenNestedArray(array $data, int $levelSize) : array
    {
        $flatArray = [];

        foreach ($data as $block) {
            if (count($block) !== $levelSize) {
                throw new ShapeMismatchException();
            }
            $flatArray = array_merge($flatArray, $block);
        }

        return $flatArray;
    }
}
