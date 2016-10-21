<?php
declare(strict_types=1);


namespace SDS\functions;


function array_fMul (int ...$arr) : float
{
    return array_reduce($arr, 'SDS\functions\fMultiply', 1.0);
}

function array_iMul (int ...$arr) : int
{
    return array_reduce($arr, 'SDS\functions\iMultiply', 1);
}

function fMultiply (float $x, float $y) : float
{
    return $x * $y;
}

function iMultiply (int $x, int $y) : int
{
    return $x * $y;
}

function isAssociativeArray(array $arr)
{
    if ([] === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function isPositive (float $x) : bool {
    return $x > 0;
}
