<?php
declare(strict_types=1);


namespace SDS\functions;


function fMultiply (float $x, float $y) : float
{
    return $x * $y;
}

function iMultiply (int $x, int $y) : int
{
    return $x * $y;
}

function array_fMul (float ...$arr) : float
{
    return (float)\array_product($arr);
}

function array_iMul (int ...$arr) : int
{
    return (int)\array_product($arr);
}

function isAssociativeArray(array $arr)
{
    return (
        [] !== $arr &&
        \array_keys($arr) !== \range(0, \count($arr) - 1)
    );
}

function isPositive (float $x) : bool
{
    return $x > 0;
}

function randBinomial(int $n, int $k=2) : int
{
    $acc = 0;

    for ($i=0; $i<$n; $i++) {
        $acc += \rand(0, $k-1);
    }

    return $acc;
}
