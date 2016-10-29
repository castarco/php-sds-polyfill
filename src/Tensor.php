<?php
declare(strict_types=1);


namespace SDS;


use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Ds\{Hashable, Vector};

use SDS\Exceptions\InvalidPermutationException;
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
        $t->setShape($shape);

        return $t;
    }

    /**
     * @param bool $inPlace
     * @return Tensor|IntTensor|FloatTensor
     */
    public function squeeze(bool $inPlace = false) : Tensor
    {
        $t = $inPlace ? $this : clone $this;
        $t->setShape(\array_values(\array_filter($t->shape, function (int $x) : bool {
            return $x > 1;
        })));

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
        $t->setShape($shape);

        return $t;
    }

    /**
     * @param int[] $tileSpec
     * @return Tensor
     */
    public function tile(array $tileSpec) : Tensor
    {
        self::checkShape(...$tileSpec);

        $nDims = \count($this->shape);
        if (\count($tileSpec) !== $nDims) {
            throw new ShapeMismatchException();
        }

        /** @var int[] $newShape */
        $newShape = \array_map('\SDS\functions\iMultiply', $this->shape, $tileSpec);
        $pointer = \array_fill(0, $nDims, 0);

        $t = new static();
        $t->setShape($newShape);
        $t->data = new Vector();
        $t->data->allocate(array_iMul(...$newShape));

        do {
            $t->data->push($this->get(...\array_map(
                function (int $p, int $s) : int { return $p % $s; },
                $pointer,
                $this->shape
            )));
        } while (self::pointerUpdater($pointer, $newShape, $nDims));

        return $t;
    }

    /**
     * @param int[] $permutation
     * @return Tensor
     */
    public function transpose(array $permutation = null) : Tensor
    {
        $nDims = \count($this->shape);

        if (null === $permutation) {
            $permutation = [];
            for ($i=$nDims-1; $i >= 0; $i--) {
                $permutation[] = $i;
            }
        } else {
            $this->checkPermutation(...$permutation);
        }

        $newShape = self::permute($this->shape, $permutation);
        $pointer = \array_fill(0, $nDims, 0);

        /** @var IntTensor|FloatTensor $t */
        $t = clone $this;
        $t->setShape($newShape);

        do {
            $t->set(
                $this->get(...$pointer),
                self::permute($pointer, $permutation)
            );
        } while (self::pointerUpdater($pointer, $this->shape, $nDims));

        return $t;
    }

    /**
     * @param null|int|bool[] $dimsToCollapse
     * @param bool $keepRedundantDims
     * @return int|float|IntTensor|FloatTensor
     */
    public function sum($dimsToCollapse = null, bool $keepRedundantDims=false)
    {
        $nDims = \count($this->shape);

        if (null === $dimsToCollapse) {
            return $this->data->sum();
        } else {
            self::checkDimsSelector($dimsToCollapse, $nDims);
        }

        $pointer = \array_fill(0, $nDims, 0);
        $newShape = self::collapseDims($this->shape, $dimsToCollapse, true);

        $t = new static();
        $t->setShape($newShape);
        $t->initWithConstant(0);

        do {
            $t->data[
                $t->getInternalIndex(...self::collapseDims($pointer, $dimsToCollapse))
            ] += $this->data[
                $this->getInternalIndex(...$pointer)
            ];
        } while(self::pointerUpdater($pointer, $this->shape, $nDims));

        return $keepRedundantDims ? $t : $t->squeeze(true);
    }

    /**
     * @param null|int|bool[] $dimsToCollapse
     * @param bool $keepRedundantDims
     * @return int|float|IntTensor|FloatTensor
     */
    public function max($dimsToCollapse = null, bool $keepRedundantDims=false)
    {
        $nDims = \count($this->shape);

        if (null === $dimsToCollapse) {
            return \max(...$this->data);
        } else {
            self::checkDimsSelector($dimsToCollapse, $nDims);
        }

        $pointer = \array_fill(0, $nDims, 0);

        $newShape = self::collapseDims($this->shape, $dimsToCollapse, true);

        $t = new static();
        $t->setShape($newShape);
        $t->initWithConstant($this instanceof IntTensor ? -PHP_INT_MAX-1 : -INF);

        do {
            $i = $t->getInternalIndex(...self::collapseDims($pointer, $dimsToCollapse));
            $t->data[$i] = \max($t->data[$i], $this->data[$this->getInternalIndex(...$pointer)]);
        } while(self::pointerUpdater($pointer, $this->shape, $nDims));

        return $keepRedundantDims ? $t : $t->squeeze(true);
    }

    /**
     * @param null|int|bool[] $dimsToCollapse
     * @param bool $keepRedundantDims
     * @return int|float|IntTensor|FloatTensor
     */
    public function min($dimsToCollapse = null, bool $keepRedundantDims=false)
    {
        $nDims = \count($this->shape);

        if (null === $dimsToCollapse) {
            return \min(...$this->data);
        } else {
            self::checkDimsSelector($dimsToCollapse, $nDims);
        }

        $pointer = \array_fill(0, $nDims, 0);

        $newShape = self::collapseDims($this->shape, $dimsToCollapse, true);

        $t = new static();
        $t->setShape($newShape);
        $t->initWithConstant($this instanceof IntTensor ? PHP_INT_MAX : +INF);

        do {
            $i = $t->getInternalIndex(...self::collapseDims($pointer, $dimsToCollapse));
            $t->data[$i] = \min($t->data[$i], $this->data[$this->getInternalIndex(...$pointer)]);
        } while(self::pointerUpdater($pointer, $this->shape, $nDims));

        return $keepRedundantDims ? $t : $t->squeeze(true);
    }

    /**
     * @param null|int|bool[] $dimsToCollapse
     * @param bool $keepRedundantDims
     * @return float|FloatTensor
     */
    public function mean($dimsToCollapse = null, bool $keepRedundantDims=false)
    {
        $nDims = \count($this->shape);

        if (null === $dimsToCollapse) {
            return $this->data->sum() / \count($this->data);
        } else {
            self::checkDimsSelector($dimsToCollapse, $nDims);
        }

        $t = new FloatTensor();
        $t->setShape(self::collapseDims($this->shape, $dimsToCollapse, true));
        $t->initWithConstant(0);

        $pointer = \array_fill(0, $nDims, 0);

        do {
            $t->data[
                $t->getInternalIndex(...self::collapseDims($pointer, $dimsToCollapse))
            ] += $this->data[
                $this->getInternalIndex(...$pointer)
            ];
        } while(self::pointerUpdater($pointer, $this->shape, $nDims));

        $divisor = 1.0;
        foreach ($dimsToCollapse as $i => $d) {
            if ($d) $divisor *= $this->shape[$i];
        }
        $t->data->apply(function (float $v) use ($divisor) : float {
            return $v / $divisor;
        });

        return $keepRedundantDims ? $t : $t->squeeze(true);
    }

    /**
     * @param null $dimsToCollapse
     * @param bool $keepRedundantDims
     * @return float|FloatTensor
     */
    public function variance($dimsToCollapse = null, bool $keepRedundantDims=false)
    {
        $nDims = \count($this->shape);

        if (null === $dimsToCollapse) {
            $mean = $this->data->sum() / \count($this->data);
            $acc = 0;
            foreach ($this->data as $x) {
                $acc += ($x-$mean)*($x-$mean);
            }
            return $acc / (\count($this->data) - 1);
        } else {
            self::checkDimsSelector($dimsToCollapse, $nDims);
        }

        $t = new FloatTensor();
        $t->setShape(self::collapseDims($this->shape, $dimsToCollapse, true));
        $t->initWithConstant(0);

        $pointer = \array_fill(0, $nDims, 0);
        do {
            $t->data[
                $t->getInternalIndex(...self::collapseDims($pointer, $dimsToCollapse))
            ] += $this->data[
                $this->getInternalIndex(...$pointer)
            ];
        } while(self::pointerUpdater($pointer, $this->shape, $nDims));

        $divisor = 1.0;
        foreach ($dimsToCollapse as $i => $d) {
            if ($d) $divisor *= $this->shape[$i];
        }
        $t->data->apply(function (float $v) use ($divisor) : float {
            return $v / $divisor;
        });

        $mean = $t->data;
        $t->initWithConstant(0);

        $pointer = \array_fill(0, $nDims, 0);
        do {
            $collapsedIndex = $t->getInternalIndex(...self::collapseDims($pointer, $dimsToCollapse));

            $t->data[$collapsedIndex] += pow(
                $this->data[$this->getInternalIndex(...$pointer)] - $mean[$collapsedIndex],
                2
            );
        } while(self::pointerUpdater($pointer, $this->shape, $nDims));

        $t->data->apply(function (float $v) use ($divisor) : float {
            return $v / ($divisor-1);
        });

        return $keepRedundantDims ? $t : $t->squeeze(true);
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
     * @param int[] &$pointer
     * @param int[] $shape
     * @param int $nDims
     * @return bool
     */
    protected static function pointerUpdater(array &$pointer, array $shape, int $nDims) : bool
    {
        for ($i=$nDims-1; $i >= 0; $i--) {
            if ($pointer[$i] < $shape[$i]-1) {
                $pointer[$i]++;

                for ($j=$i+1; $j<$nDims; $j++) {
                    $pointer[$j] = 0;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param int[] $dims
     * @param bool[] $dimsToCollapse
     * @param bool $isShape
     * @return array
     */
    protected static function collapseDims(array $dims, array $dimsToCollapse, bool $isShape = false) : array
    {
        return $isShape
            ? \array_map(
                function ($sDim, $collapse) { return $collapse ? 1 : $sDim; },
                $dims,
                $dimsToCollapse
            )
            : \array_map(
                function ($sDim, $collapse) { return $collapse ? 0 : $sDim; },
                $dims,
                $dimsToCollapse
            );
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
     * @param int[] ...$permutation
     * @throws InvalidPermutationException
     */
    private function checkPermutation(int ...$permutation)
    {
        $nDims = \count($this->shape);
        if (\count($permutation) !== $nDims) {
            throw new InvalidPermutationException();
        }

        $checked = [];
        foreach ($permutation as $pos) {
            if ($pos < 0 || $pos >= $nDims || in_array($pos, $checked)) {
                throw new InvalidPermutationException();
            }
            $checked[] = $pos;
        }
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
     * @param int|bool[] $dimsToCollapse
     * @param int $nDims
     * @throws ShapeMismatchException
     * @throws \InvalidArgumentException
     */
    private static function checkDimsSelector(&$dimsToCollapse, int $nDims)
    {
        if (is_int($dimsToCollapse)) {
            if ($dimsToCollapse < 0 || $dimsToCollapse >= $nDims) {
                throw new \InvalidArgumentException();
            }

            $tmp = \array_fill(0, $nDims, false);
            $tmp[$dimsToCollapse] = true;
            $dimsToCollapse = $tmp;
        }
        elseif (is_array($dimsToCollapse)) {
            if (\count($dimsToCollapse) !== $nDims) {
                throw new ShapeMismatchException();
            }
        }
        else {
            throw new \InvalidArgumentException();
        }
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

    /**
     * @param array $arr
     * @param int[] $permutation
     * @return array
     */
    private static function permute(array $arr, array $permutation) : array
    {
        $result = [];

        foreach ($permutation as $p) {
            $result[] = $arr[$p];
        }

        return $result;
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
