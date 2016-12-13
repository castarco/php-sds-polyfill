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
     * @param callable $fn
     * @return Matrix
     */
    public function apply(callable $fn)
    {
        $this->data->apply($fn);

        return $this;
    }

    /**
     * @param callable $fn
     * @return Matrix
     */
    public function map(callable $fn) : Matrix
    {
        $t = clone $this;
        $t->data->apply($fn);

        return $t;
    }

    /**
     * @param int $i
     * @param int $j
     * @return int|float
     */
    public abstract function get(int $i, int $j);

    /**
     * @param Matrix $b
     * @param null|Matrix $dst
     * @return Matrix
     *
     * @throws \TypeError when $dst does not math the operands types
     */
    public function matMul(Matrix $b, Matrix $dst = null) : Matrix
    {
        list($m, $n) = $this->shape;
        list($p, $q) = $b->shape;

        if ($n !== $p) {
            throw new ShapeMismatchException();
        }

        $mSize = $m * $q;

        if (null === $dst) {
            $dst = ($this instanceof IntMatrix && $b instanceof IntMatrix)
                ? new IntMatrix($m, $q)
                : new FloatMatrix($m, $q);
            $newMatData = new Vector(\array_fill(0, $mSize, 0));
            $dst->data = $newMatData;
        } else {
            if ($dst->height !== $m || $dst->width !== $q) {
                throw new ShapeMismatchException();
            } elseif ($dst instanceof IntMatrix && ($this instanceof FloatMatrix || $b instanceof FloatMatrix)) {
                throw new \TypeError('Expected $dst to be FloatMatrix');
            } elseif ($dst instanceof FloatMatrix && $this instanceof IntMatrix && $b instanceof IntMatrix) {
                throw new \TypeError('Expected $dst to be IntMatrix');
            }
            $newMatData = $dst->data;
            $newMatData->apply(function () { return 0; });
        }

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

        return $dst;
    }

    /**
     * @return Matrix
     *
     * TODO: Add in-place transposition
     * TODO: Add "symbolic" transposition (no transposition at all!)
     */
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
     * @param bool $getQ
     * @return Matrix[] [$Q, $R]
     */
    public function qrDecomposition(bool $getQ = true) : array
    {
        list($m, $n) = $this->shape;
        $k = \min($m, $n);

        $Q  = $getQ ? FloatMatrix::eye($m, 1.0, $k) : null;
        $R  = FloatMatrix::eye($k, 1.0, $m)->matMul($this);
        $H1 = FloatMatrix::eye($k);

        for ($i = 0; $i < $k; $i++) {
            $H = clone $H1;
            $slice = [$i, $k - 1];
            $H[[ $slice, $slice ]] = (
                FloatMatrix::householder($R[[ $slice, $i ]])
            );

            $Q = $getQ ? $Q->matMul($H) : null;
            $R = $H->matMul($R);
        }

        return [$Q, $R];
    }

    public function isSquare() : bool
    {
        return ($this->width === $this->height);
    }

    /**
     * @return int|float
     */
    public function trace()
    {
        $w = $this->width;
        if (($this->width !== $this->height)) {
            throw new ShapeMismatchException('The trace only can be computed over square matrix');
        }

        $mSize = $w * $w;
        $data = $this->data;
        $acc = 0;

        for ($i = 0; $i < $mSize; $i += $w + 1) {
            $acc += $data[$i];
        }

        return $acc;
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
     * @return int[]
     */
    protected function getShapeFromSliceSpec(array $sliceSpec) : array
    {
        $this->checkSliceSpec($sliceSpec);

        $shape = [];

        foreach ($sliceSpec as $dimIndex => $sComp) {
            if (null === $sComp) {
                $shape[] = $this->shape[$dimIndex];
            } elseif (\is_int($sComp)) {
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

                \is_int($sComp) && $sComp >= 0 && $sComp < $this->shape[$dimIndex] ||

                (
                    \is_array($sComp) && \count($sComp) === 2 &&
                    \is_int($sComp[0]) && $sComp[0] >= 0 &&
                    \is_int($sComp[1]) && $sComp[1] >= $sComp[0] && $sComp[1] < $this->shape[$dimIndex]
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
    public function equals($obj, $epsilon = null) : bool
    {
        if ($obj === $this) {
            return true;
        } elseif (
            \get_class($obj) !== \get_class($this) ||
            $obj->height !== $this->height ||
            $obj->width  !== $this->width
        ) {
            return false;
        }

        $tD = $this->data;
        /** @var Vector $oD */
        $oD = $obj->data;

        if (null === $epsilon) {
            return ($oD->toArray() === $tD->toArray());
        }

        $n = $tD->count();
        for ($i = 0; $i < $n; $i++) {
            $a = $tD[$i];
            $b = $oD[$i];
            if (\abs($a - $b)/( \max(\abs($a), \abs($b)) + $epsilon) > $epsilon) return false;
        }

        return true;
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
