<?php
declare(strict_types=1);


namespace SDS;


use Ds\{Hashable, Vector};
use SDS\Exceptions\ShapeMismatchException;
use function SDS\functions\isAssociativeArray;


abstract class Matrix implements \ArrayAccess, \Countable, \IteratorAggregate, Hashable
{
    /** @var int */
    protected $height;

    /** @var int */
    protected $width;

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

        $slice->data = new Vector();
        $slice->data->allocate($slice->height * $slice->width);

        for ($i=$normalizedSliceSpec[0][0]; $i<=$normalizedSliceSpec[0][1]; $i++) {
            for ($j=$normalizedSliceSpec[1][0]; $j<=$normalizedSliceSpec[1][1]; $j++) {
                $slice->data->push($this->data[$i*$this->width + $j]);
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

    /**
     * @param Matrix $b
     * @return Matrix
     */
    public function matMul(Matrix $b) : Matrix
    {
        list($m, $n) = $this->shape;
        list($p, $q) = $b->shape;

        if ($n !== $p) {
            throw new ShapeMismatchException();
        }

        $mSize = $m * $q;
        $newMatData = new Vector(\array_fill(0, $mSize, 0));
        $tD = $this->data;
        $bD = $b->data;

        // TODO: use Strassen's algorithm?
        for ($j = 0; $j < $q; $j++) {
            for ($k = 0, $kq = 0; $k < $n; $k++, $kq += $q) {
                $t = $bD[$kq + $j];
                for ($iq = 0, $in = 0; $iq < $mSize; $iq += $q, $in += $n) {
                    $newMatData[$iq + $j] += $tD[$in + $k] * $t;
                }
            }
        }

        $newMat = ($this instanceof IntMatrix && $b instanceof IntMatrix)
            ? new IntMatrix($m, $q)
            : new FloatMatrix($m, $q);
        $newMat->data = $newMatData;

        return $newMat;
    }

    public function transpose() : Matrix
    {
        list($m, $n) = $this->shape;

        $tData = $this->data;
        $newMatData = new Vector(\array_fill(0, $m * $n, 0));

        for ($i = 0, $in = 0; $i < $m; $i++, $in += $n)  {
            for ($j = 0, $jm = 0; $j < $n; $j++, $jm += $m) {
                $newMatData[$jm + $i] = $tData[$in + $j];
            }
        }

        $newMat = new static($n, $m);
        $newMat->data = $newMatData;

        return $newMat;
    }

    /**
     * Matrix constructor.
     * @param int $height
     * @param int $width
     */
    protected function __construct(int $height = 1, int $width = 1)
    {
        if ($height < 1 || $width < 1) {
            throw new \DomainException();
        }

        $this->height = $height;
        $this->width  = $width;
        $this->shape  = [&$this->height, &$this->width];
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
        return (
            \is_array($offset) && \count($offset) === 2 &&
            \is_int($offset[0]) && $offset[0] < $this->height && $offset[0] >= 0 &&
            \is_int($offset[1]) && $offset[1] < $this->width  && $offset[1] >= 0
        );
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
            if (\count($offset) !== 2) {
                throw new \OutOfRangeException();
            }
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
