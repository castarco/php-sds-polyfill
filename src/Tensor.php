<?php
declare(strict_types=1);


namespace SDS;


use Ds\{Hashable, Vector};

use SDS\Exceptions\ShapeMismatchException;
use function SDS\functions\ {
    array_iMul,
    isAssociativeArray,
    isPositive
};


abstract class Tensor implements \ArrayAccess, \Countable, \IteratorAggregate, Hashable
{
    /** @var int[] */
    protected $shape;

    /** @var int[] */
    protected $indexShifts;

    /** @var int[]|float[]|Vector */
    protected $data;

    /**
     * @return int[]
     */
    public function getShape() : array
    {
        return $this->shape;
    }

    /**
     * @param (null|int|int[])[] $sliceSpec
     * @param bool $keepRedundantDims
     * @return Tensor
     */
    public function slice(array $sliceSpec, $keepRedundantDims = false) : Tensor
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
     * @param callable $fn
     */
    public function apply(callable $fn)
    {
        $this->data->apply($fn);
    }

    /**
     * @param callable $fn
     * @return Tensor
     */
    public function map(callable $fn) : Tensor
    {
        $t = new static();
        $t->shape       = $this->shape;
        $t->indexShifts = $this->indexShifts;
        $t->data        = $this->data->map($fn);

        return $t;
    }

    /**
     * @param int[] $shape
     * @param bool $inPlace
     * @return Tensor
     */
    public function reshape(array $shape, bool $inPlace = false) : Tensor
    {
        self::checkShape(...$shape);

        if (array_iMul(...$shape) !== array_iMul(...$this->shape)) {
            throw new ShapeMismatchException();
        }

        $t = $inPlace ? $this : clone $this;
        $t->shape = $shape;

        return $t;
    }

    /**
     * @param bool $inPlace
     * @return Tensor
     */
    public function squeeze(bool $inPlace = false) : Tensor
    {
        $t = $inPlace ? $this : clone $this;
        $t->shape = \array_values(\array_filter($t->shape, function (int $x) : bool {
            return $x > 1;
        }));

        return $t;
    }

    /**
     * @param int $position
     * @param bool $inPlace
     * @return Tensor
     */
    public function addDimension(int $position, bool $inPlace = false) : Tensor
    {
        if ($position < 0 || $position > count($this->shape)) {
            throw new ShapeMismatchException();
        }

        $t = $inPlace ? $this : clone $this;

        $shape = [];
        for ($i=0; $i<count($this->shape); $i++) {
            if ($i === $position) {
                $shape[] = 1;
            }
            $shape[] = $this->shape[$i];
        }
        if ($i === $position) {
            $shape[] = 1;
        }
        $t->shape = $shape;

        return $t;
    }

    /**
     * @param int[] ...$offset
     * @return int|float
     */
    public abstract function get(int ...$offset);

    /**
     * @param int[]|array[] $source
     * @param (null|int|int[])[] $sliceSpec
     * @return void
     */
    public abstract function setArrayAsSlice(array $source, array $sliceSpec);

    /**
     * Tensor constructor.
     */
    protected function __construct() { }

    /**
     * @param int|float $c
     * @return void
     */
    protected abstract function initWithConstant($c = 0);

    /**
     * @param int[] ...$offset
     * @return int
     */
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

    /**
     * @param int[] $shape
     */
    protected function setShape(array $shape)
    {
        $this->shape       = $shape;
        $this->indexShifts = \array_fill(0, count($this->shape), 0);

        $v = 1;
        for ($i=\count($shape)-1; $i >= 0; $i--) {
            $this->indexShifts[$i] = $v;
            $v *= $shape[$i];
        }
    }

    /**
     * @param (null|int|int[])[] $sliceSpec
     * @param bool $keepRedundantDims
     * @return int[]
     */
    protected function getShapeFromSliceSpec(array $sliceSpec, bool $keepRedundantDims=false) : array
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

        return $shape;
    }

    /**
     * @param (null|int|int[])[] $sliceSpec
     * @return int[][]
     */
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

    /**
     * @param int[] ...$shape
     * @throws \InvalidArgumentException
     */
    protected static function checkShape(int ...$shape)
    {
        if (\count(\array_filter($shape, 'SDS\functions\isPositive')) < \count($shape)) {
            throw new \InvalidArgumentException('Shape dimensions must have a strictly positive width');
        }
    }

    /**
     * @param array[] $source
     * @return Tensor
     */
    protected static function fromArrayWithInferredShape(array $source) : Tensor
    {
        list($shape, $data) = self::inferShapeAndExtractData($source);

        return static::fromArrayWithForcedShape($shape, ...$data);
    }

    /**
     * @param array[] $source
     * @return array
     */
    protected static function inferShapeAndExtractData(array $source) : array
    {
        $shape = [];
        $data = $source;

        for ($i = $source; is_array($i); $i = $i[0]) {
            $shape[] = count($i);
            if (is_array($i[0])) {
                $data = static::flattenNestedArray($data, count($i[0]));
            }
        }

        return [$shape, $data];
    }

    /**
     * @param (null|int|int[])[] $sliceSpec
     * @throws ShapeMismatchException
     */
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

    /**
     * @param (null|int|int[])[] $sliceSpec
     * @return int[][]
     */
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

    /**
     * @param int[] ...$offset
     * @return bool
     */
    private function _offsetExists(int ...$offset) : bool
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

    /**
     * @param array $data
     * @param int $levelSize
     * @return int[]|float[]
     */
    private static function flattenNestedArray(array $data, int $levelSize) : array
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

    // -----------------------------------------------------------------------------------------------------------------
    // Core PHP interfaces methods
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @inheritdoc
     */
    public function __clone()
    {
        $this->data  = clone $this->data;
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
                $obj->shape === $this->shape &&
                $obj->data->toArray()  === $this->data->toArray()
            )
        );
    }

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
    public function offsetExists($offset) : bool
    {
        try {
            return $this->_offsetExists(...$offset);
        } catch (\Throwable $t) {
            return false;
        }
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param int[] $offset The offset to retrieve.
     * @return int|float|FloatTensor|IntTensor
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
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Not supported operation');
    }

    /**
     * @inheritdoc
     */
    public function count() : int
    {
        return \count($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getIterator() : \Traversable
    {
        return $this->data;
    }
}
