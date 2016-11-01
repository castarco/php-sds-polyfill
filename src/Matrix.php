<?php
declare(strict_types=1);


namespace SDS;


use Ds\{Hashable, Vector};
use SDS\Exceptions\ShapeMismatchException;
use function SDS\functions\isAssociativeArray;


abstract class Matrix implements \ArrayAccess, \Countable, \IteratorAggregate, Hashable
{
    /** @var int */
    protected $height = 1;

    /** @var int */
    protected $width = 1;

    /** @var int[] */
    protected $shape;

    /** @var int[]|float[]|Vector */
    protected $data;

    /**
     * @return int[]
     */
    public function getShape() : array
    {
        return [$this->height, $this->width];
    }

    /**
     * @param (null|int|int[])[] $sliceSpec
     * @return Matrix|IntMatrix|FloatMatrix
     */
    public function slice(array $sliceSpec) : Matrix
    {
        if (isAssociativeArray($sliceSpec) || \count($sliceSpec) !== 2) {
            throw new ShapeMismatchException();
        }

        $slice = new static();

        $slice->setShape($this->getShapeFromSliceSpec($sliceSpec));
        $normalizedSliceSpec = $this->getNormalizedSliceSpec($sliceSpec);

        $slice->initWithConstant(0);

        $dataIndex = 0;

        for ($i=$normalizedSliceSpec[0][0]; $i<=$normalizedSliceSpec[0][1]; $i++) {
            for ($j=$normalizedSliceSpec[1][0]; $j<=$normalizedSliceSpec[1][1]; $j++) {
                $slice->data[$dataIndex++] = $this->data[$i*$this->width + $j];
            }
        }

        return $slice;
    }

    /**
     * @param int $i
     * @param int $j
     * @return int|float
     */
    public abstract function get(int $i, int $j);

    protected function __construct()
    {
        $this->shape = [];
        $this->shape[] = &$this->height;
        $this->shape[] = &$this->width;
    }

    /**
     * @param int|float $c
     * @return void
     */
    protected abstract function initWithConstant($c = 0);

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
     * @param int[] $shape
     */
    protected function setShape(array $shape)
    {
        $this->shape[0] = $shape[0];
        $this->shape[1] = $shape[1];
    }

    /**
     * @param (null|int|int[])[] $sliceSpec
     * @return int[][]
     */
    protected function getNormalizedSliceSpec(array $sliceSpec) : array
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
     * @param (null|int|int[])[] $sliceSpec
     * @throws ShapeMismatchException
     */
    private function checkSliceSpec(array $sliceSpec)
    {
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
                $obj->height === $this->height &&
                $obj->width  === $this->width  &&
                $obj->data->toArray()  === $this->data->toArray()
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function hash() : int
    {
        $hash = (37 + \count($this->data)) % \PHP_INT_MAX;
        $hash = (41*$hash + $this->height) % \PHP_INT_MAX;
        $hash = (41*$hash + $this->width)  % \PHP_INT_MAX;

        foreach ($this->data as $cell) {
            $hash = (43*$hash + (int)\floor($cell)) % \PHP_INT_MAX;
        }

        return $hash;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset) : bool
    {
        try {
            return (
                \is_array($offset) && \count($offset) === 2 &&
                \is_int($offset[0]) && $offset[0] < $this->height && $offset[0] >= 0 &&
                \is_int($offset[1]) && $offset[1] < $this->width  && $offset[1] >= 0
            );
        } catch (\Throwable $t) {
            return false;
        }
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param int[] $offset The offset to retrieve.
     * @return int|float|FloatMatrix|IntMatrix
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
